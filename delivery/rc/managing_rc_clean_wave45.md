# Managing RC-clean Wave 45

Base: `Managing_rc_clean_wave44_cumulative.zip`

## Purpose

Wave45 hardens the owner-side runtime proof around Windows PowerShell compatibility after the wave44 apply-script issue showed that old Windows PowerShell can reject string-based `TrimEnd()` arguments.

## Changes

- Added `tools/runtime/assert-powershell-runtime-compatibility.ps1`.
- Added `powershell-compatibility.log` to the runtime proof results.
- Updated `tools/runtime/capture-rc-runtime-proof.ps1` to execute the PowerShell compatibility check.
- Updated `tools/runtime/write-rc-runtime-proof-intake.php` so `core_runtime_green` requires PowerShell compatibility proof.
- Updated runtime proof README and machine-readable RC status.

## Compatibility rule

Windows/PowerShell scripts must use char-based slash trimming such as `TrimEnd([char]92, [char]47)` and must not require `[System.IO.Path]::GetRelativePath()` on owner Windows runtimes.

## Verification

- `php -l` over `src`, `tests`, and `tools`: syntax OK.
- Runtime proof remains owner-side because the artifact environment has no local `vendor/` and no host application runtime context.
