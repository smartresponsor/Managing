<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$resultDir = $root.'/delivery/rc/runtime-proof-results';
if (!is_dir($resultDir) && !mkdir($resultDir, 0775, true) && !is_dir($resultDir)) {
    fwrite(STDERR, 'Unable to create runtime proof result directory.'.PHP_EOL);
    exit(1);
}

$composerValidateLog = $resultDir.'/composer-validate.log';
$powerShellCompatibilityLog = $resultDir.'/powershell-compatibility.log';
$phpunitLog = $resultDir.'/phpunit.log';
$phpstanLog = $resultDir.'/phpstan.log';
$canonLog = $resultDir.'/canon-guard.log';
$syntaxLog = $resultDir.'/syntax.log';
$hostContainerLog = $resultDir.'/host-container.log';
$hostEasyAdminRoutesLog = $resultDir.'/host-easyadmin-routes.log';

$statuses = [
    'syntax' => classifyLogStatus($syntaxLog, ['PASS: PHP syntax check passed.']),
    'canon_guard' => classifyLogStatus($canonLog, ['Managing canon guard passed.']),
    'composer_validate' => classifyLogStatus($composerValidateLog, ['is valid', 'is valid, but']),
    'powershell_compatibility' => classifyLogStatus($powerShellCompatibilityLog, ['PASS: PowerShell compatibility baseline passed.']),
    'phpunit' => classifyLogStatus($phpunitLog, ['OK (', 'OK, but', 'No tests executed!'], ['FAILURES!', 'ERRORS!', 'There was ', 'There were ']),
    'phpstan' => classifyLogStatus($phpstanLog, ['[OK]', 'No errors', '100%'], ['[ERROR]', 'Found ', 'Command failed']),
    'host_container' => classifyLogStatus($hostContainerLog, ['Symfony', 'Kernel'], ['Exception', 'Command failed']),
    'host_easyadmin_routes' => classifyLogStatus($hostEasyAdminRoutesLog, ['PASS: Manage/EasyAdmin route surface was found'], ['FAIL: Manage/EasyAdmin route surface was not found', 'Command failed']),
];

$coreRuntimeGreen = allPassed($statuses, ['syntax', 'canon_guard', 'composer_validate', 'powershell_compatibility', 'phpunit', 'phpstan']);
$hostIntegrationGreen = allPassed($statuses, ['host_container', 'host_easyadmin_routes']);
$runtimeGreen = $coreRuntimeGreen;

$summary = [
    'component' => 'Managing',
    'phase' => 'rc-runtime-proof-prep',
    'generated_at_utc' => gmdate('c'),
    'overall_status' => $runtimeGreen ? 'runtime_green' : 'runtime_review_required',
    'logs' => [
        'syntax' => relativePath($root, $syntaxLog),
        'canon_guard' => relativePath($root, $canonLog),
        'composer_validate' => relativePath($root, $composerValidateLog),
        'powershell_compatibility' => relativePath($root, $powerShellCompatibilityLog),
        'phpunit' => relativePath($root, $phpunitLog),
        'phpstan' => relativePath($root, $phpstanLog),
        'host_container' => relativePath($root, $hostContainerLog),
        'host_easyadmin_routes' => relativePath($root, $hostEasyAdminRoutesLog),
    ],
    'status' => $statuses,
    'core_runtime_green' => $coreRuntimeGreen,
    'host_integration_green' => $hostIntegrationGreen,
    'runtime_green' => $runtimeGreen,
    'rc_claim' => $runtimeGreen
        ? 'Core runtime proof logs indicate syntax, canon guard, Composer validation, PowerShell compatibility, PHPUnit, and PHPStan passed. Host-application container/EasyAdmin proof is reported separately.'
        : 'No runtime-green claim is made by this intake. Logs are the source of truth.',
];

$jsonPath = $resultDir.'/runtime-proof-intake.json';
file_put_contents($jsonPath, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

$coreRuntimeGreenText = bool($summary['core_runtime_green']);
$hostIntegrationGreenText = bool($summary['host_integration_green']);

$markdown = <<<MD
# Managing RC Runtime Proof Intake

Generated UTC: {$summary['generated_at_utc']}

Overall status: **{$summary['overall_status']}**

Core runtime green: **{$coreRuntimeGreenText}**

Host integration green: **{$hostIntegrationGreenText}**

| Check | Status | Log |
|---|---:|---|
| syntax | {$summary['status']['syntax']} | {$summary['logs']['syntax']} |
| canon guard | {$summary['status']['canon_guard']} | {$summary['logs']['canon_guard']} |
| Composer validate | {$summary['status']['composer_validate']} | {$summary['logs']['composer_validate']} |
| PowerShell compatibility | {$summary['status']['powershell_compatibility']} | {$summary['logs']['powershell_compatibility']} |
| PHPUnit | {$summary['status']['phpunit']} | {$summary['logs']['phpunit']} |
| PHPStan | {$summary['status']['phpstan']} | {$summary['logs']['phpstan']} |
| host container | {$summary['status']['host_container']} | {$summary['logs']['host_container']} |
| host EasyAdmin routes | {$summary['status']['host_easyadmin_routes']} | {$summary['logs']['host_easyadmin_routes']} |

{$summary['rc_claim']}
MD;

file_put_contents($resultDir.'/runtime-proof-intake.md', $markdown.PHP_EOL);

echo 'Managing RC runtime proof intake written.'.PHP_EOL;

function bool(bool $value): string
{
    return $value ? 'true' : 'false';
}

/**
 * @param array<string, string> $statuses
 * @param list<string> $keys
 */
function allPassed(array $statuses, array $keys): bool
{
    foreach ($keys as $key) {
        if (($statuses[$key] ?? null) !== 'pass') {
            return false;
        }
    }

    return true;
}

function normalizeLogContents(string $contents): string
{
    if (str_starts_with($contents, "\xff\xfe") || str_starts_with($contents, "\xfe\xff")) {
        $converted = @mb_convert_encoding($contents, 'UTF-8', 'UTF-16');
        if (is_string($converted)) {
            $contents = $converted;
        }
    }

    return str_replace("\0", '', $contents);
}

function relativePath(string $root, string $path): string
{
    $root = str_replace('\\', '/', rtrim($root, '/\\'));
    $path = str_replace('\\', '/', $path);

    if (str_starts_with($path, $root.'/')) {
        return substr($path, strlen($root) + 1);
    }

    return $path;
}

/**
 * @param list<string> $passNeedles
 * @param list<string> $failureNeedles
 */
function classifyLogStatus(string $path, array $passNeedles, array $failureNeedles = []): string
{
    if (!is_file($path)) {
        return 'missing';
    }

    $contents = normalizeLogContents((string) file_get_contents($path));
    if (trim($contents) === '') {
        return 'missing';
    }

    if (str_contains($contents, 'SKIP:')) {
        return 'skipped';
    }

    foreach (array_merge(['FAIL:', 'Fatal error', 'PHP Fatal error', 'Parse error', 'Command failed'], $failureNeedles) as $needle) {
        if (str_contains($contents, $needle)) {
            return 'failed';
        }
    }

    foreach ($passNeedles as $needle) {
        if (str_contains($contents, $needle)) {
            return 'pass';
        }
    }

    return 'captured_review_required';
}
