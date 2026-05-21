# Managing RC clean wave 18

## Scope

Wave 18 continues from `Managing_rc_clean_wave17_cumulative.zip` and focuses on the remaining abstract CRUD controller mass.

## Changes

- Added `ManageCrudControllerRuntimeInjectionTrait`.
- Added `ManageCrudControllerSurfaceTrait`.
- Moved setter-injection/runtime accessor boilerplate out of `AbstractManageContentCrudController`.
- Moved private entity-surface/publication guard helpers out of `AbstractManageContentCrudController`.
- Reduced `AbstractManageContentCrudController` from about 333 lines to about 160 lines.
- Preserved generated-controller compatibility and manual `new` test compatibility.

## Validation

- `php -l` passed for all PHP files under `src`, `tests`, and `tools`.
- PHPUnit/PHPStan were not run in the archive environment because runtime dependencies are not installed.

## Remaining RC notes

The abstract CRUD controller is now close to an orchestration-only EasyAdmin adapter. Remaining RC passes should focus on configuration tree maintainability and final runtime proof in the host application.
