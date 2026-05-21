= Managing RC Clean Wave 5

Base: `Managing_rc_clean_wave4_cumulative.zip`.

== Goal

Move the remaining generic CRUD behavior candidate lists out of `AbstractManageContentCrudController` and into a reusable policy service so search/status/publication/sort defaults are controlled by one descriptor-oriented layer instead of controller-local string arrays.

== Changes

* Added `App\Managing\Service\Crud\ManageCrudBehaviorPolicy`.
* Added configurable policy nodes for:
** `crud_behavior_search_fields`
** `crud_behavior_status_fields`
** `crud_behavior_publication_flag_fields`
** `crud_behavior_publication_date_fields`
** `crud_behavior_audit_date_fields`
** `crud_behavior_default_sort_fields`
* Updated `AbstractManageContentCrudController` so default search/status/publication/date/sort candidates come from `ManageCrudBehaviorPolicy`.
* Preserved component override hooks by keeping protected static methods as runtime overrides.
* Added `#[Required]` setter injection hooks so Symfony can inject configured services while manual `new` construction in unit tests remains supported through safe fallbacks.
* Added unit coverage for the new behavior policy.

== Verification

* `php -l` over `src/`, `tests/`, and `tools/`: PASS.
* PHPUnit/PHPStan were not run because the archive does not include `vendor/`.

== Current Risk Notes

The controller is now materially less hardcoded, but its line count increased because setter injection and helper methods were added to preserve generated-controller compatibility. The remaining controller cleanup should focus on moving EasyAdmin action construction and filter construction into dedicated factories, not on changing routing or generated controller inheritance.
