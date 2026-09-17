Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$root = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
Set-Location $root

$requiredSiblings = @(
    '..\Administering',
    '..\Cruding',
    '..\Interfacing',
    '..\Objecting',
    '..\Rolling',
    '..\Viewing'
)

foreach ($sibling in $requiredSiblings) {
    if (-not (Test-Path -LiteralPath $sibling -PathType Container)) {
        throw "Required sibling repository is missing: $sibling"
    }
}

$packages = @(
    'administering/administration',
    'cruding/crud',
    'interfacing/interface',
    'objecting/object',
    'rolling/role',
    'viewing/view'
)

Write-Host 'Resolving Managing first-party dependency contour...'
& composer update @packages --with-dependencies --no-interaction
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host 'Validating Composer manifest and lock...'
& composer validate --strict --check-lock --no-interaction
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

& composer run verify:first-party-dependencies
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host 'Linting PHP sources...'
$phpFiles = Get-ChildItem -Path src,tests,tools -Recurse -File -Filter '*.php'
foreach ($file in $phpFiles) {
    & php -l $file.FullName | Out-Host
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}

if (Test-Path -LiteralPath 'vendor\bin\phpstan.bat') {
    Write-Host 'Running PHPStan...'
    & 'vendor\bin\phpstan.bat' analyse
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
} elseif (Test-Path -LiteralPath 'vendor\bin\phpstan') {
    Write-Host 'Running PHPStan...'
    & php 'vendor\bin\phpstan' analyse
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}

if (Test-Path -LiteralPath 'vendor\bin\phpunit.bat') {
    Write-Host 'Running PHPUnit...'
    & 'vendor\bin\phpunit.bat'
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
} elseif (Test-Path -LiteralPath 'vendor\bin\phpunit') {
    Write-Host 'Running PHPUnit...'
    & php 'vendor\bin\phpunit'
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}

if (Test-Path -LiteralPath 'bin\console') {
    Write-Host 'Running Symfony container/YAML gates...'
    & php bin\console lint:container
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

    if (Test-Path -LiteralPath 'config') {
        & php bin\console lint:yaml config --parse-tags
        if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
    }

    & php bin\console doctrine:schema:validate --skip-sync
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}

$gating = Join-Path $root '..\Gating\bin\gating'
if (Test-Path -LiteralPath $gating -PathType Leaf) {
    Write-Host 'Running Gating...'
    & php $gating check --target=$root
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}

Write-Host 'Managing Composer contour closure: PASS'
