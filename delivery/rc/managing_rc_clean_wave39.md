# Managing RC-clean wave 39

Base: `Managing_rc_clean_wave38_cumulative.zip`

## Goal

Prepare the repository for owner-side runtime proof without claiming runtime green from the artifact environment.

## Changes

- Added `tools/runtime/capture-rc-runtime-proof.ps1`.
- Added `tools/runtime/write-rc-runtime-proof-intake.php`.
- Added `delivery/rc/runtime-proof-results/.gitkeep`.
- The runtime proof script captures:
  - PHP syntax check logs;
  - Managing canon guard logs;
  - PHPUnit logs when `vendor/bin/phpunit` exists;
  - PHPStan logs when `vendor/bin/phpstan` exists.
- The intake writer creates:
  - `delivery/rc/runtime-proof-results/runtime-proof-intake.json`;
  - `delivery/rc/runtime-proof-results/runtime-proof-intake.md`.

## RC note

This wave does not make a runtime-green claim. The local logs remain the source of truth.

## Suggested local command

```powershell
powershell -ExecutionPolicy Bypass -File "tools/runtime/capture-rc-runtime-proof.ps1" -ProjectRoot "D:\PhpstormProjects\www\Managing"
```
