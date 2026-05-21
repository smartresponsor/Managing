# Managing runtime-fix wave 47

Base: `Managing_rc_clean_wave46_cumulative.zip`.

Purpose: close the remaining owner-side runtime proof failures after wave46.

## Fixed

- `ManageFilesystemPathNormalizer::isAbsolutePath()` now treats all Unix-rooted paths as absolute and uses a PowerShell/Windows-safe drive-letter regex for Windows paths.
- Host entity discovery now benefits from the corrected path normalization, preventing Doctrine mapping directories from being resolved as nested relative paths.
- `tools/manage-canon-guard.php` now normalizes Windows paths before relative-path checks.
- Generated CRUD bridge controllers are explicitly excluded from the symbol-prefix guard.
- Existing intentional Managing/AttachmentIdentifierMigration symbols are allowed by the canon guard.

## Local verification in this build environment

- PHP syntax check passed for touched PHP files.
- `php tools/manage-canon-guard.php` passes inside the patched snapshot.

## Owner-side next proof

Run:

```powershell
powershell -ExecutionPolicy Bypass -File "tools/runtime/capture-rc-runtime-proof.ps1" -ProjectRoot "D:\PhpstormProjects\www\Managing"
php tools/runtime/assert-rc-runtime-proof.php
```

PHPStan may remain skipped until `vendor/bin/phpstan` is installed.
