# Managing RC Clean Wave 6

## Scope

Wave 6 continues the RC-clean refactor from `Managing_rc_clean_wave5_cumulative.zip`.

## Changes

- Split concrete EasyAdmin field construction out of `ManageCrudFieldFactory`.
- Added `ManageEasyAdminFieldBuilder` as the field-type mapping and EasyAdmin field creation service.
- Added `ManageCrudFieldLabeler` for deterministic property-to-label formatting.
- Reduced `ManageCrudFieldFactory` to field ordering and discovery orchestration.
- Preserved manual-instantiation compatibility for generated CRUD controller tests.
- Added `ManageCrudFieldLabelerTest`.

## Why

After wave 5, `ManageCrudFieldFactory` was the largest remaining CRUD class and still mixed ordering,
reflection traversal, EasyAdmin type mapping, label formatting, enum conversion, association rendering and
string heuristics. This wave separates those responsibilities without changing generated controller contracts.

## Proof

- `php -l` was run over `src`, `tests` and `tools`.
- PHPUnit/PHPStan were not run in the artifact environment because dependencies are not installed in the archive.
