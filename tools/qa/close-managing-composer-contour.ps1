Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

$root = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
Set-Location $root

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

foreach ($sibling in $requiredSiblings) {
    if (-not (Test-Path -LiteralPath $sibling -PathType Container)) {
        throw "Required sibling repository is missing: $sibling"
    }
}

Write-Host 'Resolving complete Managing dependency graph...'
& composer update --with-all-dependencies --no-interaction
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

$phpstan = if (Test-Path -LiteralPath 'vendor\bin\phpstan.bat' -PathType Leaf) {
    'vendor\bin\phpstan.bat'
} elseif (Test-Path -LiteralPath 'vendor\bin\phpstan' -PathType Leaf) {
    'vendor\bin\phpstan'
} else {
    throw 'Required PHPStan executable is missing from vendor\bin.'
}

Write-Host 'Running PHPStan...'
if ($phpstan.EndsWith('.bat')) {
    & $phpstan analyse
} else {
    & php $phpstan analyse
}
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

$phpunit = if (Test-Path -LiteralPath 'vendor\bin\phpunit.bat' -PathType Leaf) {
    'vendor\bin\phpunit.bat'
} elseif (Test-Path -LiteralPath 'vendor\bin\phpunit' -PathType Leaf) {
    'vendor\bin\phpunit'
} else {
    throw 'Required PHPUnit executable is missing from vendor\bin.'
}

Write-Host 'Running PHPUnit...'
if ($phpunit.EndsWith('.bat')) {
    & $phpunit
} else {
    & php $phpunit
}
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

if (Test-Path -LiteralPath 'bin\console' -PathType Leaf) {
    Write-Host 'Running Symfony container/YAML gates...'
    & php bin\console lint:container
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

    if (Test-Path -LiteralPath 'config' -PathType Container) {
        & php bin\console lint:yaml config --parse-tags
        if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
    }

    & php bin\console doctrine:schema:validate --skip-sync
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
} else {
    Write-Host 'No bundle-local bin\console present; host/container Symfony gates remain a separate acceptance step.'
}

$gating = Join-Path $root '..\Gating\bin\gating'
if (-not (Test-Path -LiteralPath $gating -PathType Leaf)) {
    throw "Required Gating executable is missing: $gating"
}

Write-Host 'Running Gating...'
& php $gating check --target=$root
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

Write-Host 'Managing Composer contour closure: PASS'
