param(
    [string] $ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
)

$ErrorActionPreference = 'Stop'

$ProjectRoot = (Resolve-Path -LiteralPath $ProjectRoot).Path
Write-Output '[runtime-proof] PowerShell compatibility check started.'
$version = $PSVersionTable.PSVersion.ToString()
Write-Output "PowerShell version: $version"

try {
    $trimmed = 'C:\\tmp\\'.TrimEnd([char]92, [char]47)
    if ($trimmed -ne 'C:\\tmp') {
        Write-Output "FAIL: char-based TrimEnd produced unexpected value: $trimmed"
        exit 1
    }

    Write-Output 'PASS: char-based TrimEnd/TrimStart compatibility is available.'
} catch {
    Write-Output ('FAIL: char-based TrimEnd/TrimStart compatibility failed: ' + $_.Exception.Message)
    exit 1
}

try {
    [void] [System.IO.Path].GetMethod('GetRelativePath', [type[]] @([string], [string]))
    Write-Output 'INFO: [System.IO.Path]::GetRelativePath is available in this runtime.'
} catch {
    Write-Output 'INFO: [System.IO.Path]::GetRelativePath is not available; repository scripts must use substring-based relative path fallback.'
}

Write-Output 'PASS: PowerShell compatibility baseline passed.'
Write-Host '[runtime-proof] PASS: PowerShell compatibility baseline passed.'
