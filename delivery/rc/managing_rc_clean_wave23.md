# Managing RC-clean wave 23

Base: `Managing_rc_clean_wave22_cumulative.zip`

## Goal

Continue RC hardening by narrowing the EasyAdmin field builder surface without changing generated CRUD controller contracts.

## Changes

- Added `ManageEasyAdminAssociationFieldFactory` for Doctrine association field construction.
- Added `ManageEasyAdminArrayChoiceFieldFactory` for array-backed `ChoiceField` construction.
- Reduced `ManageEasyAdminFieldBuilder` so it keeps scalar/enum orchestration and delegates special field construction.
- Added `ManageEasyAdminSpecialFieldFactoriesTest` for relation and array choice field factory contracts.

## Validation

- `php -l` was executed over `src`, `tests`, and `tools`.
- PHPUnit/PHPStan were not executed because the archive does not include runtime `vendor/`.

## Notes

This wave does not add business features. It keeps the RC-clean direction: smaller collaborators, explicit seams, and stable generated controller behavior.
