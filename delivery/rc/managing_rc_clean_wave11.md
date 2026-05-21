= Managing RC Clean Wave 11

Base: Managing_rc_clean_wave10_cumulative.zip

== Scope

Wave 11 removes the remaining root-entity-to-component hardcode from the host class-name resolver and moves it into the existing CRUD resource policy/config seam.

== Changes

* Added policy-driven root entity component resolution through `ManageCrudResourcePolicy::componentKeyFromRootName()`.
* Added `component_root_aliases` configuration for root entity aliases that cannot be represented by the component root-name map alone, for example `Category => cataloging`.
* Wired `ManageHostClassNameResolver` to `ManageCrudResourcePolicy` through Symfony services.
* Preserved manual instantiation compatibility by allowing `ManageHostClassNameResolver` to run without an injected policy and fall back to slug-based resolution.
* Updated `ManageHostApplicationAdminProvider` manual fallback to pass the same policy instance into the class-name resolver.
* Added unit coverage for policy-driven root resolution and host class-name component resolution.

== RC effect

The host discovery layer no longer carries a local component-specific map inside `ManageHostClassNameResolver`. Component-name inference now belongs to the policy/config layer with the rest of the CRUD resource selection rules.

== Validation

* `php -l` over all PHP files in `src`, `tests`, and `tools`: PASS.
* PHPUnit/PHPStan not executed in the artifact environment because `vendor/` is not present.
