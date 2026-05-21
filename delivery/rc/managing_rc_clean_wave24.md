# Managing RC-clean wave 24

## Base

Active base: `Managing_rc_clean_wave23_cumulative.zip`.

## Goal

Remove duplicated host filesystem path normalization from host discovery services and keep Windows/Unix path handling in one narrow collaborator.

## Changes

- Added `App\Managing\Service\Filesystem\ManageFilesystemPathNormalizer`.
- Updated `ManageHostPathResolver` to delegate:
  - relative-to-project path resolution;
  - slash normalization;
  - `realpath()` fallback handling.
- Updated `ManageHostPsr4RootResolver` to delegate:
  - Composer PSR-4 relative root normalization;
  - default `src` root path generation;
  - `realpath()` fallback handling.
- Preserved manual construction compatibility through constructor default collaborator instances.
- Added unit coverage for shared path normalization and relative Composer PSR-4 roots.

## Validation

- `php -l` passed for all PHP files under `src`, `tests`, and `tools`.
- Runtime PHPUnit/PHPStan were not executed because the archive does not include runtime `vendor/`.

## RC impact

This wave reduces small but risky path-handling drift in host discovery. It is especially relevant for the user's Windows/PowerShell environment because path logic now has one explicit service instead of repeated inline path checks.
