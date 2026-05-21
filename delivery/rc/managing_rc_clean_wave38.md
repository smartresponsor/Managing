# Managing RC-clean wave 38

## Goal

Close the generated CRUD / EasyAdmin contract audit pass before RC by making generated controller rewrites preserve intentional checked-in bridge customizations.

## Changes

- Added `ManageGeneratedCrudControllerCustomizationExtractor`.
- `ManageGeneratedCrudControllerWriter` now reads an existing generated controller before rewriting it.
- Protected `manage*` customization hooks are preserved during deterministic generated source refresh.
- `ManageGeneratedCrudControllerSourceRenderer` accepts a custom-methods block while still owning the generated route/entity shell.
- Added tests covering customization extraction and preservation of `manageArrayChoiceFields()` hooks.

## Why

Generated controllers are thin bridge classes. Most are fully generated, but some checked-in bridge controllers intentionally carry component-facing EasyAdmin overrides. Before this wave, a generator run could overwrite those hooks even though architecture tests allowed generated controllers as bridge exceptions.

## Validation

- `php -l` over `src`, `tests`, and `tools`: PASS.
- PHPUnit/PHPStan were not executed in this archive-only environment because runtime `vendor/` is not present.
