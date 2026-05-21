= Managing RC Clean Wave 29

== Base

Active base: `Managing_rc_clean_wave28_cumulative.zip`.

== Goal

Split the remaining publication-state service seam so entity field state access is separated from batch/entity-manager orchestration.

== Changes

* Added `ManagePublicationFieldStateAccessor`.
* Moved reflection-backed publication flag/date state resolution and mutation out of `ManagePublicationStateHandler`.
* Kept `ManagePublicationStateHandler` as the stable orchestration facade for publication support checks, row action predicates, single entity mutation, and EasyAdmin batch mutation.
* Preserved default manual-construction compatibility for generated CRUD controllers and tests.
* Added `ManagePublicationFieldStateAccessorTest`.

== Verification

* `php -l` over `src`, `tests`, and `tools`: OK.
* PHPUnit/PHPStan were not run in this artifact environment because runtime `vendor/` is not present in the archive.
