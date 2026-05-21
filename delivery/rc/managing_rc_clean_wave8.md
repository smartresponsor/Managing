= Managing RC Clean Wave 8

Base archive: `Managing_rc_clean_wave7_cumulative.zip`.

== Goal

Reduce generated CRUD fragility by separating resource selection from deterministic controller source rendering.

== Changes

* Extracted generated controller PHP rendering into `App\Managing\Service\Admin\ManageGeneratedCrudControllerWriter`.
* Reduced `ManageCrudControllerGenerator` to resource grouping, primary-resource selection and orchestration.
* Centralized the legacy Attaching identifier migration bridge in `ManageAttachmentIdentifierMigrationTrait` instead of embedding the full migration bootstrap into generated controller templates.
* Updated `AttachingCrudController` to use the migration trait and keep a small deterministic `index()` override.
* Registered the generated controller writer in `config/services.yaml`.
* Added `ManageGeneratedCrudControllerWriterTest` to cover read-only controller rendering and attachment migration bridge rendering.

== RC effect

Before this wave, `ManageCrudControllerGenerator` mixed:

* resource scoring orchestration;
* generated path management;
* PHP source templating;
* stale controller deletion;
* read-only controller method emission;
* Attaching-specific migration controller body emission.

After this wave:

* the generator selects what should be generated;
* the writer owns how generated controllers are rendered and written;
* Attaching migration remains config-driven through `ManageCrudResourcePolicy`, but its runtime bridge is no longer duplicated as a long inline template block.

== Verification

`php -l` was executed across `src`, `tests` and `tools`; all checked PHP files passed syntax validation.

PHPUnit/PHPStan were not executed in this archive-only environment because `vendor/` is not present.
