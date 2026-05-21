# Managing RC-clean wave 26

Base: `Managing_rc_clean_wave25_cumulative.zip`.

## Goal

Continue dependency-injection cleanup by splitting CRUD default vocabulary into focused providers instead of keeping resource, behavior, and field vocabulary in one holder.

## Changes

- Added `ManagingCrudResourceConfigurationDefaults` for generated CRUD/resource-scoring defaults.
- Added `ManagingCrudBehaviorConfigurationDefaults` for CRUD search/status/publication/sort defaults.
- Added `ManagingCrudFieldConfigurationDefaults` for field-discovery vocabulary.
- Kept `ManagingCrudConfigurationDefaults` as a backward-compatible facade so `Configuration.php` and existing extension code do not need to change.
- Added `ManagingCrudConfigurationDefaultsSplitTest` to pin facade delegation.

## Verification

- `php -l` passed for `src`, `tests`, and `tools`.
- PHPUnit/PHPStan were not run because this archive does not include runtime `vendor/`.
