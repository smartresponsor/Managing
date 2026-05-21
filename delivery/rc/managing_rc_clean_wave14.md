# Managing RC-clean wave 14

Base: `Managing_rc_clean_wave13_cumulative.zip`

## Goal

Reduce the remaining orchestration weight in `AbstractManageContentCrudController` without changing the generated controller contract.

## Changes

- Extracted controller override/default hook methods into `ManageCrudControllerCustomizationTrait`.
- Added `ManageCrudPublicationWorkflow` as an EasyAdmin-facing publication action workflow around `ManagePublicationStateHandler`.
- Updated `AbstractManageContentCrudController` to depend on the publication workflow instead of directly orchestrating publication state mutation.
- Preserved manual `new GeneratedCrudController()` compatibility through lazy fallback service creation.
- Added unit coverage for the extracted customization hook trait and publication workflow predicate behavior.

## Result

- `AbstractManageContentCrudController` reduced from about 430 lines to about 351 lines.
- Component-specific CRUD controllers keep the same protected static override contract.
- Publication mutation rules remain in `ManagePublicationStateHandler`; EasyAdmin action flow now sits in `ManageCrudPublicationWorkflow`.

## Verification

- `php -l` was run across `src`, `tests`, and `tools`.
- PHPUnit/PHPStan were not run because this archive does not contain runtime `vendor/`.
