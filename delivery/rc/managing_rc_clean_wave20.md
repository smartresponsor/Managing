# Managing RC-clean wave 20

Base: `Managing_rc_clean_wave19_cumulative.zip`.

## Goal

Move large default configuration vocabularies out of the Symfony Config tree builder so `Configuration` remains a schema definition layer instead of a policy/default-value bag.

## Changes

- Added `src/DependencyInjection/ManagingConfigurationDefaults.php`.
- Moved default lists/maps for menu, host scan, component roots, CRUD resource policy, CRUD behavior policy, and CRUD field policy into the defaults class.
- Reduced `src/DependencyInjection/Configuration.php` from about 241 lines to about 177 lines.
- Added `tests/Unit/DependencyInjection/ManagingConfigurationDefaultsTest.php` to pin key canonical defaults.

## Validation

- `php -l` passed for all PHP files under `src`, `tests`, and `tools`.
- PHPUnit/PHPStan were not executed because this archive does not include the runtime `vendor/` directory.
