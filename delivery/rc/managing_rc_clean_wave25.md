# Managing RC-clean wave 25

Base: `Managing_rc_clean_wave24_cumulative.zip`.

## Goal

Reduce the remaining host PSR-4 discovery responsibility by separating Composer metadata reading from root resolution.

## Changes

- Added `ManageHostComposerAutoloadRootReader` as the dedicated Composer autoload reader.
- Reduced `ManageHostPsr4RootResolver` from about 158 lines to 107 lines.
- Kept `ManageHostPsr4RootResolver` responsible for root selection, fallback root handling, namespace exclusion and de-duplication.
- Wired the new reader in `config/services.yaml`.
- Preserved manual construction compatibility through lazy fallback creation of the reader.
- Added `ManageHostComposerAutoloadRootReaderTest`.

## Verification

- `php -l` over `src`, `tests`, and `tools`: PASS.
- PHP files checked: 115.
- PHPUnit/PHPStan were not executed because the archive does not include runtime `vendor/`.

## Remaining RC notes

The main EasyAdmin CRUD and host discovery god-objects are already split. Remaining work should be small hardening passes, runtime proof on the real repository with vendor installed, and final owner-review/RC report consolidation.
