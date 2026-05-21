# Managing RC-clean wave 27

## Base

Active base: `Managing_rc_clean_wave26_cumulative.zip`.

## Goal

Split the remaining broad reflection inspector into field-access and metadata collaborators without changing the existing `ManageEntityReflectionInspector` facade contract used by CRUD/runtime services.

## Changes

- Added `ManageEntityFieldAccessor` for has/read/write/existing field behavior.
- Added `ManageEntityMetadataInspector` for property type, attribute instance, and enum column metadata resolution.
- Reduced `ManageEntityReflectionInspector` to a stable facade over the two collaborators.
- Added unit coverage for accessor, metadata inspector, and facade compatibility.

## Validation

- `php -l` across `src`, `tests`, and `tools`: pass.
- PHPUnit/PHPStan were not run in this artifact environment because runtime `vendor/` is not included in the archive.
