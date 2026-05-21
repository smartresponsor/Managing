= Managing RC Clean Wave 13

Base snapshot: `Managing_rc_clean_wave12_cumulative.zip`.

== Goal

Continue RC hardening by reducing the remaining runtime burden inside `AbstractManageContentCrudController` without adding new business features.

== Changes

* Added `App\Managing\Service\Crud\ManageCrudEntitySurfaceResolver`.
* Moved entity-surface resolution out of the base CRUD controller:
** entity singular/plural label resolution;
** configured + runtime search/status/publication candidate merging;
** existing-field filtering for EasyAdmin search/filter surfaces;
** default sort resolution.
* Added `#[Required]` setter injection hook for the new resolver while keeping manual `new GeneratedCrudController()` compatibility through a lazy fallback.
* Added `tests/Unit/Crud/ManageCrudEntitySurfaceResolverTest.php` to cover:
** default and explicit labels;
** configured candidate filtering;
** runtime candidate override behavior;
** default sort selection.

== Verification

* `php -l` passed for all PHP files under `src`, `tests`, and `tools`.
* PHPUnit/PHPStan were not executed because this archive does not include runtime `vendor/`.

== Remaining RC debt

* `AbstractManageContentCrudController` is still the largest runtime orchestration class, but it now delegates another responsibility set to a narrow service.
* Next hardening candidates: publication action orchestration and further controller method grouping, or additional tests around full EasyAdmin configureCrud/configureFilters integration in a host fixture.
