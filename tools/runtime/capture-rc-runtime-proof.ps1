param(
    [string] $ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path,
    [string] $HostRoot = ''
)

$ErrorActionPreference = 'Stop'

function Write-LogLine {
    param(
        [string] $Path,
        [string] $Message
    )

    Add-Content -LiteralPath $Path -Value $Message
}

function Invoke-And-Capture {
    param(
        [string] $LogPath,
        [scriptblock] $Command
    )

    try {
        & $Command *>&1 | Tee-Object -FilePath $LogPath
        if ($LASTEXITCODE -ne $null -and $LASTEXITCODE -ne 0) {
            Write-LogLine -Path $LogPath -Message "Command failed with exit code $LASTEXITCODE"
            return $false
        }

        return $true
    } catch {
        Write-LogLine -Path $LogPath -Message $_.Exception.Message
        return $false
    }
}


function Resolve-PhpStanCommand {
    param(
        [string] $Root
    )

    $Candidates = @(
        (Join-Path $Root 'vendor\bin\phpstan.bat'),
        (Join-Path $Root 'vendor\bin\phpstan'),
        (Join-Path $Root 'vendor\bin\phpstan.php'),
        (Join-Path $Root 'vendor\phpstan\phpstan\phpstan'),
        (Join-Path $Root 'vendor\phpstan\phpstan\phpstan.phar')
    )

    foreach ($Candidate in $Candidates) {
        if (Test-Path -LiteralPath $Candidate) {
            return (Resolve-Path -LiteralPath $Candidate).Path
        }
    }

    return ''
}

function Write-PhpStanMissingLog {
    param(
        [string] $LogPath,
        [string] $Root
    )

    $Checked = @(
        (Join-Path $Root 'vendor\bin\phpstan.bat'),
        (Join-Path $Root 'vendor\bin\phpstan'),
        (Join-Path $Root 'vendor\bin\phpstan.php'),
        (Join-Path $Root 'vendor\phpstan\phpstan\phpstan'),
        (Join-Path $Root 'vendor\phpstan\phpstan\phpstan.phar')
    )

    Set-Content -LiteralPath $LogPath -Value 'SKIP: PHPStan executable was not found. Add/install PHPStan before static runtime proof.'
    Add-Content -LiteralPath $LogPath -Value 'Checked paths:'
    foreach ($Path in $Checked) {
        Add-Content -LiteralPath $LogPath -Value ('- ' + $Path)
    }

    $VendorBin = Join-Path $Root 'vendor\bin'
    if (Test-Path -LiteralPath $VendorBin) {
        Add-Content -LiteralPath $LogPath -Value 'vendor/bin candidates:'
        $Matches = Get-ChildItem -LiteralPath $VendorBin -File -Filter '*phpstan*' -ErrorAction SilentlyContinue
        if ($Matches -eq $null -or $Matches.Count -eq 0) {
            Add-Content -LiteralPath $LogPath -Value '- none'
        } else {
            foreach ($Match in $Matches) {
                Add-Content -LiteralPath $LogPath -Value ('- ' + $Match.FullName)
            }
        }
    } else {
        Add-Content -LiteralPath $LogPath -Value 'vendor/bin directory is missing.'
    }
}

function Resolve-ConsolePath {
    param(
        [string] $Root
    )

    $Console = Join-Path $Root 'bin\console'
    if (Test-Path -LiteralPath $Console) {
        return $Console
    }

    $ConsoleBat = Join-Path $Root 'bin\console.bat'
    if (Test-Path -LiteralPath $ConsoleBat) {
        return $ConsoleBat
    }

    return ''
}

$ProjectRoot = (Resolve-Path -LiteralPath $ProjectRoot).Path
$ResultDir = Join-Path $ProjectRoot 'delivery\rc\runtime-proof-results'
New-Item -ItemType Directory -Force -Path $ResultDir | Out-Null

$SyntaxLog = Join-Path $ResultDir 'syntax.log'
$CanonLog = Join-Path $ResultDir 'canon-guard.log'
$ComposerValidateLog = Join-Path $ResultDir 'composer-validate.log'
$PowerShellCompatibilityLog = Join-Path $ResultDir 'powershell-compatibility.log'
$PhpUnitLog = Join-Path $ResultDir 'phpunit.log'
$PhpStanLog = Join-Path $ResultDir 'phpstan.log'
$HostContainerLog = Join-Path $ResultDir 'host-container.log'
$HostEasyAdminRoutesLog = Join-Path $ResultDir 'host-easyadmin-routes.log'

Set-Content -LiteralPath $SyntaxLog -Value '[runtime-proof] PHP syntax check started.'
$SyntaxOk = $true
$PhpFiles = Get-ChildItem -LiteralPath $ProjectRoot -Recurse -File -Filter '*.php' |
    Where-Object {
        $_.FullName -notlike '*\vendor\*' -and
        $_.FullName -notlike '*\.git\*'
    }

foreach ($File in $PhpFiles) {
    $Output = & php -l $File.FullName 2>&1
    Add-Content -LiteralPath $SyntaxLog -Value $Output
    if ($LASTEXITCODE -ne 0) {
        $SyntaxOk = $false
    }
}

if ($SyntaxOk) {
    Add-Content -LiteralPath $SyntaxLog -Value 'PASS: PHP syntax check passed.'
} else {
    Add-Content -LiteralPath $SyntaxLog -Value 'FAIL: PHP syntax check failed.'
}

$CanonOk = Invoke-And-Capture -LogPath $CanonLog -Command { php (Join-Path $ProjectRoot 'tools\manage-canon-guard.php') }


$ComposerCommand = Get-Command composer -ErrorAction SilentlyContinue
if ($ComposerCommand -ne $null) {
    $ComposerValidateOk = Invoke-And-Capture -LogPath $ComposerValidateLog -Command { composer validate --strict --no-check-publish --working-dir $ProjectRoot }
} else {
    Set-Content -LiteralPath $ComposerValidateLog -Value 'SKIP: composer command is missing. Install Composer or run validation manually before runtime-green assertion.'
    $ComposerValidateOk = $false
}

$PowerShellCompatibilityScript = Join-Path $ProjectRoot 'tools\runtime\assert-powershell-runtime-compatibility.ps1'
if (Test-Path -LiteralPath $PowerShellCompatibilityScript) {
    $PowerShellCompatibilityOk = Invoke-And-Capture -LogPath $PowerShellCompatibilityLog -Command { powershell -ExecutionPolicy Bypass -File $PowerShellCompatibilityScript -ProjectRoot $ProjectRoot }
} else {
    Set-Content -LiteralPath $PowerShellCompatibilityLog -Value 'FAIL: tools/runtime/assert-powershell-runtime-compatibility.ps1 is missing.'
    $PowerShellCompatibilityOk = $false
}

$PhpUnit = Join-Path $ProjectRoot 'vendor\bin\phpunit.bat'
if (-not (Test-Path -LiteralPath $PhpUnit)) {
    $PhpUnit = Join-Path $ProjectRoot 'vendor\bin\phpunit'
}

if (Test-Path -LiteralPath $PhpUnit) {
    $PhpUnitOk = Invoke-And-Capture -LogPath $PhpUnitLog -Command { & $PhpUnit -c (Join-Path $ProjectRoot 'phpunit.xml.dist') }
} else {
    Set-Content -LiteralPath $PhpUnitLog -Value 'SKIP: vendor/bin/phpunit is missing. Run composer install before runtime proof.'
    $PhpUnitOk = $false
}

$PhpStan = Resolve-PhpStanCommand -Root $ProjectRoot
if ($PhpStan -ne '') {
    Set-Content -LiteralPath $PhpStanLog -Value ('[runtime-proof] PHPStan executable: ' + $PhpStan)
    $PhpStanOk = Invoke-And-Capture -LogPath $PhpStanLog -Command {
        if ($PhpStan.ToLowerInvariant().EndsWith('.phar') -or $PhpStan.ToLowerInvariant().EndsWith('.php')) {
            php $PhpStan analyse src tests
        } else {
            & $PhpStan analyse src tests
        }
    }
} else {
    Write-PhpStanMissingLog -LogPath $PhpStanLog -Root $ProjectRoot
    $PhpStanOk = $false
}

$HostContainerOk = $false
$HostEasyAdminRoutesOk = $false
if ($HostRoot -ne '') {
    $ResolvedHostRoot = (Resolve-Path -LiteralPath $HostRoot).Path
    $Console = Resolve-ConsolePath -Root $ResolvedHostRoot

    if ($Console -ne '') {
        $HostContainerOk = Invoke-And-Capture -LogPath $HostContainerLog -Command { php $Console about --no-interaction }

        php $Console debug:router --no-interaction *>&1 | Tee-Object -FilePath $HostEasyAdminRoutesLog
        if ($LASTEXITCODE -eq 0) {
            $RouteText = Get-Content -LiteralPath $HostEasyAdminRoutesLog -Raw
            if ($RouteText -match '/manage' -or $RouteText -match 'manage') {
                Add-Content -LiteralPath $HostEasyAdminRoutesLog -Value 'PASS: Manage/EasyAdmin route surface was found in host router output.'
                $HostEasyAdminRoutesOk = $true
            } else {
                Add-Content -LiteralPath $HostEasyAdminRoutesLog -Value 'FAIL: Manage/EasyAdmin route surface was not found in host router output.'
            }
        } else {
            Add-Content -LiteralPath $HostEasyAdminRoutesLog -Value "Command failed with exit code $LASTEXITCODE"
        }
    } else {
        Set-Content -LiteralPath $HostContainerLog -Value 'SKIP: HostRoot was provided, but bin/console was not found.'
        Set-Content -LiteralPath $HostEasyAdminRoutesLog -Value 'SKIP: HostRoot was provided, but bin/console was not found.'
    }
} else {
    Set-Content -LiteralPath $HostContainerLog -Value 'SKIP: HostRoot was not provided. Pass -HostRoot "D:\PhpstormProjects\www\app" for host integration proof.'
    Set-Content -LiteralPath $HostEasyAdminRoutesLog -Value 'SKIP: HostRoot was not provided. Pass -HostRoot "D:\PhpstormProjects\www\app" for host integration proof.'
}

php (Join-Path $ProjectRoot 'tools\runtime\write-rc-runtime-proof-intake.php')

if ($SyntaxOk -and $CanonOk -and $ComposerValidateOk -and $PowerShellCompatibilityOk -and $PhpUnitOk -and $PhpStanOk -and (($HostRoot -eq '') -or ($HostContainerOk -and $HostEasyAdminRoutesOk))) {
    Write-Host '[runtime-proof] PASS: required checks completed for the requested scope.'
    exit 0
}

Write-Host '[runtime-proof] Completed with missing/skipped/failed checks. Review delivery/rc/runtime-proof-results.'
exit 1
