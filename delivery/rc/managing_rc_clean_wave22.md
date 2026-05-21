# Managing RC-clean wave 22

Base: `Managing_rc_clean_wave21_cumulative.zip`.

## Goal

Continue RC hardening by separating form-field reflection traversal from the ordered CRUD field surface.

## Changes

- Added `Service/Crud/ManageCrudFormFieldDiscovery`.
- Moved create/edit form property traversal out of `ManageCrudFieldFactory`.
- Kept `ManageCrudFieldFactory` focused on ordered surface orchestration.
- Preserved manual `new ManageCrudFieldFactory()` compatibility by lazily constructing the discovery service from the same policy/builder collaborators.
- Added `tests/Unit/Crud/ManageCrudFormFieldDiscoveryTest.php`.

## Verification

- `php -l` passed for PHP files under `src`, `tests`, and `tools`.
- PHPUnit/PHPStan were not run in the artifact workspace because runtime `vendor/` is not present.
