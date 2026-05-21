# Managing RC-clean wave 7

Base: `Managing_rc_clean_wave6_cumulative.zip`.

## Goal

Continue RC-hardening after the CRUD god-object split by reducing the next scalar-field construction seam inside the EasyAdmin CRUD field layer.

## Changes

- Split scalar EasyAdmin field construction out of `ManageEasyAdminFieldBuilder`.
- Added `ManageEasyAdminScalarFieldFactory` for explicit field type overrides, PHP scalar type mapping and Doctrine column type mapping.
- Added `ManageBackedEnumChoiceBuilder` so enum choice labels are no longer buried inside the EasyAdmin field builder.
- Updated `ManageEasyAdminFieldBuilder` to remain a high-level field orchestrator for ID/title/status/publication/association/array fields.
- Added `ManageBackedEnumChoiceBuilderTest` to lock the enum choice label contract.

## Current effect

- `ManageEasyAdminFieldBuilder` is now focused on field orchestration and association/array-specific behavior.
- Scalar field construction is independently testable and reusable.
- Enum choice generation is isolated from EasyAdmin field building.
- No generated CRUD controller constructor changes were introduced.
- Manual `new` compatibility is preserved through default constructor fallbacks.

## Validation

- `php -l` passed for all PHP files under `src`, `tests` and `tools`.
- PHPUnit/PHPStan were not run in this archive context because `vendor/` is not included.

## Remaining RC debt

- `AbstractManageContentCrudController` is still larger than ideal and remains the orchestration center for EasyAdmin action/filter/page configuration.
- `Configuration.php` and `ManagingExtension.php` are now large because config descriptors are explicit; they may need grouping/documentation rather than aggressive shrinking.
- Runtime proof still needs to be executed in the real repository with installed dependencies.
