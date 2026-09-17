Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$root = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
Set-Location $root

$reportDir = Join-Path $root 'delivery\rc\runtime-proof-results'
New-Item -ItemType Directory -Force -Path $reportDir | Out-Null

$summaryPath = Join-Path $reportDir 'managing-closure-summary.json'
$results = [ordered]@{}
$overallExit = 0

function Invoke-LoggedGate {
    param(
        [Parameter(Mandatory = $true)][string]$Name,
        [Parameter(Mandatory = $true)][scriptblock]$Command
    )

    $logPath = Join-Path $reportDir ("managing-{0}.log" -f $Name)
    Write-Host ("[{0}] running..." -f $Name)

    try {
        & $Command *> $logPath
        $exitCode = $LASTEXITCODE
        if ($null -eq $exitCode) { $exitCode = 0 }
    } catch {
        $_ | Out-String | Set-Content -Path $logPath -Encoding UTF8
        $exitCode = 1
    }

    $status = if ($exitCode -eq 0) { 'PASS' } else { 'FAIL' }
    $results[$Name] = [ordered]@{
        status = $status
        exit_code = [int]$exitCode
        log = (Resolve-Path -Relative $logPath).Replace('\', '/')
    }

    Write-Host ("[{0}] {1}" -f $Name, $status)
    if ($exitCode -ne 0) { $script:overallExit = 1 }

    return [int]$exitCode
}

$requiredSiblings = @(
    '..\Administering',
    '..\Collectioning',
    '..\Configuring',
    '..\Cruding',
    '..\Interfacing',
    '..\Objecting',
    '..\Rolling',
    '..\Tabling',
    '..\Viewing'
)

$missingSiblings = @($requiredSiblings | Where-Object { -not (Test-Path -LiteralPath $_ -PathType Container) })
if ($missingSiblings.Count -gt 0) {
    $results['siblings'] = [ordered]@{ status = 'FAIL'; missing = $missingSiblings }
    $results | ConvertTo-Json -Depth 8 | Set-Content -Path $summaryPath -Encoding UTF8
    Write-Host '[siblings] FAIL'
    Write-Host ("Report: {0}" -f $summaryPath)
    exit 1
}
$results['siblings'] = [ordered]@{ status = 'PASS' }

$composerExit = Invoke-LoggedGate -Name 'composer-update' -Command {
    & composer update --with-all-dependencies --no-interaction
}
if ($composerExit -ne 0) {
    $results['overall'] = [ordered]@{ status = 'FAIL'; generated_at = (Get-Date).ToString('o') }
    $results | ConvertTo-Json -Depth 8 | Set-Content -Path $summaryPath -Encoding UTF8
    Write-Host 'Overall: FAIL'
    Write-Host ("Report: {0}" -f $summaryPath)
    exit 1
}

Invoke-LoggedGate -Name 'composer-validate' -Command {
    & composer validate --strict --check-lock --no-interaction
} | Out-Null

Invoke-LoggedGate -Name 'dependency-contour' -Command {
    & composer run verify:first-party-dependencies
} | Out-Null

Invoke-LoggedGate -Name 'php-lint' -Command {
    $phpFiles = Get-ChildItem -Path src,tests,tools -Recurse -File -Filter '*.php'
    foreach ($file in $phpFiles) {
        & php -l $file.FullName
        if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
    }
} | Out-Null

$phpstan = if (Test-Path -LiteralPath 'vendor\bin\phpstan.bat' -PathType Leaf) {
    'vendor\bin\phpstan.bat'
} elseif (Test-Path -LiteralPath 'vendor\bin\phpstan' -PathType Leaf) {
    'vendor\bin\phpstan'
} else {
    $null
}

if ($null -eq $phpstan) {
    $results['phpstan'] = [ordered]@{ status = 'FAIL'; exit_code = 1; error = 'Required PHPStan executable is missing from vendor/bin.' }
    $overallExit = 1
    Write-Host '[phpstan] FAIL'
} else {
    Invoke-LoggedGate -Name 'phpstan' -Command {
        $args = @('analyse', 'src', 'tests', 'tools', '--level=8', '--memory-limit=1G', '--no-progress')
        if ($phpstan.EndsWith('.bat')) { & $phpstan @args } else { & php $phpstan @args }
    } | Out-Null
}

$phpunit = if (Test-Path -LiteralPath 'vendor\bin\phpunit.bat' -PathType Leaf) {
    'vendor\bin\phpunit.bat'
} elseif (Test-Path -LiteralPath 'vendor\bin\phpunit' -PathType Leaf) {
    'vendor\bin\phpunit'
} else {
    $null
}

if ($null -eq $phpunit) {
    $results['phpunit'] = [ordered]@{ status = 'FAIL'; exit_code = 1; error = 'Required PHPUnit executable is missing from vendor/bin.' }
    $overallExit = 1
    Write-Host '[phpunit] FAIL'
} else {
    Invoke-LoggedGate -Name 'phpunit' -Command {
        if ($phpunit.EndsWith('.bat')) { & $phpunit --colors=never } else { & php $phpunit --colors=never }
    } | Out-Null
}

if (Test-Path -LiteralPath 'bin\console' -PathType Leaf) {
    Invoke-LoggedGate -Name 'symfony-container' -Command { & php bin\console lint:container } | Out-Null

    if (Test-Path -LiteralPath 'config' -PathType Container) {
        Invoke-LoggedGate -Name 'symfony-yaml' -Command { & php bin\console lint:yaml config --parse-tags } | Out-Null
    }

    Invoke-LoggedGate -Name 'doctrine-schema' -Command { & php bin\console doctrine:schema:validate --skip-sync } | Out-Null
} else {
    $results['host-symfony'] = [ordered]@{
        status = 'SKIP'
        reason = 'No bundle-local bin/console; host/container composition acceptance remains separate.'
    }
    Write-Host '[host-symfony] SKIP'
}

$gating = Join-Path $root '..\Gating\bin\gating'
if (-not (Test-Path -LiteralPath $gating -PathType Leaf)) {
    $results['gating'] = [ordered]@{ status = 'FAIL'; exit_code = 1; error = "Required Gating executable is missing: $gating" }
    $overallExit = 1
    Write-Host '[gating] FAIL'
} else {
    Invoke-LoggedGate -Name 'gating' -Command { & php $gating check --target=$root } | Out-Null
}

$results['overall'] = [ordered]@{
    status = if ($overallExit -eq 0) { 'PASS' } else { 'FAIL' }
    generated_at = (Get-Date).ToString('o')
}
$results | ConvertTo-Json -Depth 8 | Set-Content -Path $summaryPath -Encoding UTF8

Write-Host ("Overall: {0}" -f $results['overall'].status)
Write-Host ("Report: {0}" -f $summaryPath)
exit $overallExit
