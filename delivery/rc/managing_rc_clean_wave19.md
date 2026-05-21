# Managing RC-clean wave 19

Base: `Managing_rc_clean_wave18_cumulative.zip`

## Goal

Continue RC-hardening by splitting explicit EasyAdmin field type override resolution out of the generic field discovery policy.

## Changes

- Added `App\Managing\Service\Crud\ManageCrudFieldTypeOverridePolicy`.
- Kept `ManageCrudFieldPolicy` focused on discovery vocabulary and name-based fallback classification.
- Moved configured/runtime field type override normalization and precedence into the new override policy.
- Added service wiring for the new policy in `config/services.yaml`.
- Added unit coverage for configured overrides, runtime override precedence, and invalid type rejection.

## Validation

- `php -l` passed for all PHP files under `src`, `tests`, and `tools`.
- PHPUnit/PHPStan were not executed because this archive does not include runtime `vendor/`.

## RC impact

This wave removes another mixed responsibility from the CRUD field policy and makes override behavior independently testable/configurable. It also preserves manual controller instantiation compatibility by keeping a fallback constructor path.
