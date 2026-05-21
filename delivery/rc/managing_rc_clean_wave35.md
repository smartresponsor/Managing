# Managing RC-clean wave 35

Base: `Managing_rc_clean_wave34_cumulative.zip`.

## Goal

Split the remaining resource scoring responsibility out of `ManageCrudResourcePolicy` so the policy remains focused on component/resource inclusion and root-name resolution, while primary resource ranking lives in a narrow scorer service.

## Changes

- Added `App\Managing\Service\Admin\ManageCrudResourceScorer`.
- Moved primary CRUD resource scoring rules out of `ManageCrudResourcePolicy`.
- Kept `ManageCrudResourcePolicy::scoreResource()` as the stable facade used by generator code.
- Wired the scorer in `config/services.yaml` with the existing config-driven scoring parameters.
- Preserved manual construction compatibility: `new ManageCrudResourcePolicy(...)` still works without container injection.
- Added `ManageCrudResourceScorerTest` for business-vs-technical resource ranking behavior.

## Validation

- `php -l` passed for all PHP files under `src`, `tests`, and `tools`.
- Runtime PHPUnit/PHPStan were not executed because the archive does not include runtime `vendor/`.
