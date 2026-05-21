# Managing RC-clean wave 33

Base: `Managing_rc_clean_wave32_cumulative.zip`

## Goal

Move host CRUD resource discovery out of `ManageHostApplicationAdminProvider` so the provider remains a thin admin-provider facade and the discovery/cache/entity eligibility flow has its own testable service seam.

## Changes

- Added `App\Managing\Service\Admin\Host\ManageHostCrudResourceDiscovery`.
- Moved host entity resource discovery into the new service:
  - cache load/store flow;
  - entity file eligibility;
  - Doctrine entity/id checks;
  - class-name resolution;
  - component-specific include policy;
  - resource factory creation;
  - stale cache validation.
- Reduced `ManageHostApplicationAdminProvider` from a discovery orchestrator to a thin provider/cache facade.
- Preserved manual construction compatibility used by bundle/compile-time flows.
- Added unit coverage for the new discovery seam.

## Verification

- `php -l` over `src`, `tests`, and `tools`: PASS.
- PHPUnit/PHPStan were not executed because the archive does not contain runtime `vendor/`.
