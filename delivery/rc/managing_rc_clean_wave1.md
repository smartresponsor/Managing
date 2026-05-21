# Managing RC-clean wave 1

Base archive: `Managing(2).zip`
Base SHA-256: `cf12e62867a2476a7c09dd25624e3187b72c7c3fe2d8a18b6b225451547de6f1`

## Scope

This wave starts the RC-clean refactor from the current slice only. It targets the largest confirmed SOLID violation in the slice: the oversized `AbstractManageContentCrudController` god object.

## Changes

- Extracted generic value/string rendering into `ManageEntityStringifier`.
- Extracted entity reflection helpers into `ManageEntityReflectionInspector`.
- Extracted constructor-heavy entity creation into `ManageEntityInstantiator`.
- Extracted association label resolution into `ManageAssociationLabelResolver`.
- Extracted publication state and batch publication mutation into `ManagePublicationStateHandler`.
- Extracted EasyAdmin field discovery/factory logic into `ManageCrudFieldFactory`.
- Reduced `AbstractManageContentCrudController` from 996 lines to 391 lines.
- Removed component-specific Applicating role hardcode from the generic CRUD base class.
- Moved Applicating role choices into `ApplicatingCrudController::manageArrayChoiceFields()` as an explicit component override.

## Validation performed in this environment

- `php -l` passed for all PHP files under `src`, `tests`, and `tools`.
- Full PHPUnit was not run because this archive does not include `vendor/bin/phpunit`.

## Remaining RC risks

- `ManageHostApplicationAdminProvider` is still large and should be split in the next wave into scanner, Doctrine mapping reader, cache repository, resource factory, component resolver, and CRUD controller resolver.
- `ManageCrudFieldFactory` still contains heuristic fallback logic. The next RC step should introduce explicit descriptors/config overrides so heuristics become a fallback rather than the primary contract.
- `AttachingCrudController::index()` still performs migration wiring directly in a generated controller and should be moved to a service/event/bootstrap path.
