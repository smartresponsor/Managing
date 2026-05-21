<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$intakePath = $root.'/delivery/rc/runtime-proof-results/runtime-proof-intake.json';
$requireHost = in_array('--require-host', $argv, true);

if (!is_file($intakePath)) {
    fwrite(STDERR, 'Runtime proof intake is missing: delivery/rc/runtime-proof-results/runtime-proof-intake.json'.PHP_EOL);
    fwrite(STDERR, 'Run tools/runtime/capture-rc-runtime-proof.ps1 first.'.PHP_EOL);
    exit(1);
}

$decoded = json_decode((string) file_get_contents($intakePath), true);
if (!is_array($decoded)) {
    fwrite(STDERR, 'Runtime proof intake is not valid JSON.'.PHP_EOL);
    exit(1);
}

$coreRuntimeGreen = (bool) ($decoded['core_runtime_green'] ?? false);
$hostIntegrationGreen = (bool) ($decoded['host_integration_green'] ?? false);
$statuses = is_array($decoded['status'] ?? null) ? $decoded['status'] : [];

fwrite(STDOUT, 'Managing RC runtime proof assertion'.PHP_EOL);
fwrite(STDOUT, 'Core runtime green: '.boolText($coreRuntimeGreen).PHP_EOL);
fwrite(STDOUT, 'Host integration green: '.boolText($hostIntegrationGreen).PHP_EOL);
fwrite(STDOUT, 'Host integration required: '.boolText($requireHost).PHP_EOL);

foreach (['syntax', 'canon_guard', 'composer_validate', 'phpunit', 'phpstan', 'host_container', 'host_easyadmin_routes'] as $key) {
    $status = is_string($statuses[$key] ?? null) ? $statuses[$key] : 'missing';
    fwrite(STDOUT, sprintf('- %s: %s%s', $key, $status, PHP_EOL));
}

if (!$coreRuntimeGreen) {
    fwrite(STDERR, 'FAIL: Core runtime proof is not green. Review delivery/rc/runtime-proof-results/runtime-proof-intake.md'.PHP_EOL);
    exit(1);
}

if ($requireHost && !$hostIntegrationGreen) {
    fwrite(STDERR, 'FAIL: Host integration proof is required but not green. Review host-container.log and host-easyadmin-routes.log.'.PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'PASS: Runtime proof assertion passed for requested scope.'.PHP_EOL);
exit(0);

function boolText(bool $value): string
{
    return $value ? 'true' : 'false';
}
