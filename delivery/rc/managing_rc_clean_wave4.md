= Managing RC-clean wave 4

Base archive: `Managing_rc_clean_wave3_cumulative.zip`.

== Scope

Wave 4 introduces a field descriptor/policy layer for EasyAdmin CRUD field discovery.
The goal is to keep the generic controller/factory compatible with heterogeneous host application entities while moving name-based heuristics into explicit, configurable policy.

== Changes

* Added `App\Managing\Service\Crud\ManageCrudFieldPolicy`.
* Moved title/identity/email/url/long-text fallback vocabulary out of `ManageCrudFieldFactory`.
* Added explicit field type override support.
* Added `AbstractManageContentCrudController::manageFieldTypeOverrides()` for constructor-free generated controllers.
* Added DI/config nodes for shared field policy defaults:
** `crud_field_title_candidates`
** `crud_field_identity_candidates`
** `crud_field_email_keywords`
** `crud_field_url_keywords`
** `crud_field_long_text_keywords`
** `crud_field_type_overrides`
* Wired `ManageCrudFieldPolicy` into `services.yaml` for service-mode usage.
* Added `ManageCrudFieldPolicyTest`.

== RC impact

The generic CRUD surface is less heuristic-hidden:

* field discovery vocabulary is centralized;
* component/entity-specific overrides can be declared without editing the generic factory;
* generated controllers remain constructor-free and EasyAdmin-compatible;
* legacy fallback behavior is preserved for host entities that do not yet expose descriptors.

== Validation

`php -l` was run against all PHP files under `src`, `tests` and `tools`.

Result: syntax OK.

PHPUnit/PHPStan were not run because the archive does not include `vendor/`.

== Remaining RC debt

* Replace remaining static candidate methods in `AbstractManageContentCrudController` with a broader resource descriptor object when the host provider/generator can feed descriptors safely.
* Add integration proof in a real host application with `vendor/` installed.
* Decide whether field type overrides should be emitted by generated CRUD controllers, YAML config, or host resource metadata as the canonical source.
