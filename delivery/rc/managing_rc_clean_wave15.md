# Managing RC-clean wave 15

## Base

Active base: `Managing_rc_clean_wave14_cumulative.zip`.

## Goal

Reduce the remaining controller-side service-construction responsibility in `AbstractManageContentCrudController` without changing the generated CRUD controller contract.

## Changes

- Added `src/Controller/Crud/ManageCrudControllerRuntime.php`.
- Moved lazy fallback service graph for the generic CRUD controller into `ManageCrudControllerRuntime`.
- Kept Symfony `#[Required]` setter-injection hooks on `AbstractManageContentCrudController`.
- Kept manual `new GeneratedCrudController()` compatibility for unit tests and generator smoke checks.
- Reduced `AbstractManageContentCrudController` from about 351 lines to about 333 lines.
- Added `tests/Unit/Crud/ManageCrudControllerRuntimeTest.php`.

## Runtime note

`ManageCrudControllerRuntime` is intentionally a controller-runtime holder, not a domain service. It exists to isolate Symfony/manual-construction compatibility logic from EasyAdmin orchestration.

## Verification

- `php -l` passed for all PHP files under `src`, `tests`, and `tools`.
- PHPUnit/PHPStan were not executed because the archive does not include runtime `vendor/`.
