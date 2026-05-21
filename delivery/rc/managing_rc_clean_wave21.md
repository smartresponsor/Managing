# Managing RC clean wave 21

Base: `Managing_rc_clean_wave20_cumulative.zip`

## Goal

Split the large dependency-injection defaults holder into focused configuration-default providers without changing the public configuration tree contract.

## Changes

- Added `ManagingMenuConfigurationDefaults` for menu defaults.
- Added `ManagingHostConfigurationDefaults` for host-discovery defaults.
- Added `ManagingCrudConfigurationDefaults` for CRUD policy/default vocabulary.
- Kept `ManagingConfigurationDefaults` as a backward-compatible facade used by the existing Symfony configuration tree.
- Updated dependency-injection defaults tests to prove facade delegation and preserve canonical mappings.

## Validation

- `php -l` was executed for PHP files under `src`, `tests`, and `tools`.
- PHPUnit/PHPStan were not executed because this archive does not include runtime `vendor/`.
