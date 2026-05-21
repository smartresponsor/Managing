# Managing RC Clean Wave 2

Base: `Managing_rc_clean_wave1_cumulative.zip`.

## Scope

This wave splits the host application discovery provider into focused Symfony-oriented services while preserving its public provider contract and compile-time usage from `ManagingExtension`.

## Changed architecture

`ManageHostApplicationAdminProvider` is now an orchestration provider instead of a scanner/cache/factory god object.

Extracted services:

- `Service/Admin/Host/ManageHostPathResolver.php`
  - source root resolution
  - Composer/runtime PSR-4 root discovery
  - PHP file iteration
  - excluded namespace checks
  - path normalization
- `Service/Admin/Host/ManageHostClassNameResolver.php`
  - class-name token parsing
  - component/resource key derivation
  - labels, slug/studly/humanize helpers
- `Service/Admin/Host/ManageHostDoctrineEntityInspector.php`
  - Doctrine entity attribute detection
  - single identifier gate
  - Doctrine mapping read from `config/packages/doctrine.yaml`
  - mapped-class validation
- `Service/Admin/Host/ManageHostCrudControllerResolver.php`
  - generated/host CRUD controller candidate resolution
- `Service/Admin/Host/ManageHostCrudResourceFactory.php`
  - `ManageCrudResourceDefinition` construction
- `Service/Admin/Host/ManageHostCrudResourceCache.php`
  - cache read/write and payload hydration

## Provider reduction

- `ManageHostApplicationAdminProvider.php`: 725 lines -> 175 lines.
- `AbstractManageContentCrudController.php` remains at 391 lines from wave 1.

## Compatibility notes

- The provider constructor keeps the original scalar/config arguments.
- Optional extracted collaborators are accepted for container injection.
- When the provider is manually instantiated by `ManagingExtension`, it lazily creates collaborators with the same config values.
- `config/services.yaml` now wires scalar arguments for host discovery helper services.

## Remaining debt

- `ManageHostClassNameResolver::componentKeyFromClass()` still contains the root entity -> component fallback map. This should become descriptor/config-driven in the next wave.
- `ManageHostApplicationAdminProvider::isExcludedManageResource()` still contains the Tagging-specific `TagAdminView` exception. This should move to a host resource policy/descriptor layer.
- Generated controller policy still needs formal canon: checked-in deterministic bridge vs generated build/cache artifact.

## Verification

- `php -l` was run across `src`, `tests`, and `tools`.
- Result: syntax OK.
- PHPUnit/PHPStan were not run in this artifact environment because the archive does not include `vendor/`.
