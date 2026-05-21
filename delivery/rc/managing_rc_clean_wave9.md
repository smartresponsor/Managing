# Managing RC-clean wave 9

Base: `Managing_rc_clean_wave8_cumulative.zip`.

## Goal

Continue the RC-hardening of the generated EasyAdmin CRUD surface by moving UI configuration responsibilities out of `AbstractManageContentCrudController` and into narrow service seams.

## Changes

- Added `ManageCrudPageConfigurator` for EasyAdmin CRUD page-level defaults.
- Added `ManageCrudActionConfigurator` for shared EasyAdmin action vocabulary, publish/unpublish row actions and publication batch actions.
- Added `ManageCrudFilterConfigurator` for shared status/publication/date filters.
- Updated `AbstractManageContentCrudController` to orchestrate those services through Symfony `#[Required]` setters while preserving manual `new` compatibility for unit tests and generated CRUD controllers.
- Added `ManageCrudPageConfiguratorTest` as a behavioral contract for page-level CRUD defaults.

## RC impact

The base CRUD controller now owns less EasyAdmin configuration detail. It still owns the route methods and entity-specific orchestration, but reusable page/action/filter configuration is no longer embedded directly in the controller.

## Verification

- `php -l` was executed over PHP files under `src`, `tests`, and `tools`.
- Syntax check passed.
- PHPUnit/PHPStan were not executed in this archive environment because `vendor/` is not present.
