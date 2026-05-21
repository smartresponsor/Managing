# Managing RC-clean wave 3

Base: `Managing_rc_clean_wave2_cumulative.zip`.

## Goal

Move component-specific resource-selection and primary CRUD selection rules out of generic host-discovery/generator code and into an explicit policy/config layer.

## Changes

- Added `App\Managing\Service\Admin\ManageCrudResourcePolicy`.
- Replaced generic provider hardcode for Tagging admin-view inclusion with policy-driven inclusion rules.
- Replaced generator hardcode for component root names with `component_root_names` policy data.
- Replaced Tagging/Messaging primary-resource scoring branches with policy-driven bonus/penalty suffix maps.
- Replaced generated Attaching migration branch trigger with policy-driven `crud_generated_attachment_migration_components`.
- Added configuration nodes for resource policy defaults.
- Wired policy through `ManagingExtension` and `config/services.yaml`.
- Added `ManageCrudResourcePolicyTest` to lock policy behavior.

## Validation

- `php -l` passed for all PHP files under `src`, `tests`, and `tools`.
- PHPUnit/PHPStan were not run here because the archive does not contain `vendor/`.

## Remaining RC debt

- `ManageCrudControllerGenerator` still embeds the generated controller template and the concrete attachment migration method body. The component trigger is now policy-driven, but the generated migration implementation can be extracted in a later wave if required.
- Field heuristics in `ManageCrudFieldFactory` are still pragmatic and should eventually receive descriptor overrides.
- Full runtime proof still requires running Composer dependencies in the local repository.
