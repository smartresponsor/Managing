# Managing RC-clean wave 32

Base: `Managing_rc_clean_wave31_cumulative.zip`

## Goal

Split constructor-heavy entity instantiation so the generic entity instantiator no longer mixes object construction orchestration with per-parameter placeholder resolution.

## Changes

- Added `App\Managing\Service\Crud\ManageConstructorArgumentResolver`.
- Moved constructor-parameter placeholder logic out of `ManageEntityInstantiator`.
- Kept `ManageEntityInstantiator` as the stable public facade used by CRUD controller runtime.
- Preserved manual construction compatibility: `new ManageEntityInstantiator()` still works through a lazy fallback resolver.
- Added `tests/Unit/Crud/ManageConstructorArgumentResolverTest.php`.

## Size impact

- `ManageEntityInstantiator` reduced from roughly 121 lines to 74 lines.
- Constructor argument rules now live in a focused resolver service.

## Validation

- Ran `php -l` across `src`, `tests`, and `tools`.
- Syntax check passed for 132 PHP files.
- PHPUnit/PHPStan were not run because this archive does not include runtime `vendor/`.

## RC note

This keeps the EasyAdmin pragmatic fallback for constructor-heavy host entities, but makes the fallback more SOLID: entity instantiation orchestrates, while constructor argument policy resolves safe placeholders.
