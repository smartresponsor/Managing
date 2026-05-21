# Managing RC-clean wave 44

## Active base

`Managing_rc_clean_wave43_cumulative.zip`

## Purpose

Wave 44 strengthens the owner-side runtime proof gate by making Composer package validation an explicit core proof signal. This prevents a repository from being marked runtime-green when PHP syntax/tests/static analysis pass but the package metadata or dependency constraints are invalid.

## Changes

- Updated `tools/runtime/capture-rc-runtime-proof.ps1` to capture `composer validate --strict --no-check-publish` into `delivery/rc/runtime-proof-results/composer-validate.log`.
- Updated `tools/runtime/write-rc-runtime-proof-intake.php` to classify `composer_validate` and include it in `core_runtime_green`.
- Updated `tools/runtime/assert-rc-runtime-proof.php` to print the Composer validation status during assertion.
- Updated `delivery/rc/runtime-proof-results/README.md` with the new expected log and runtime-green contract.
- Updated `delivery/rc/managing_rc_status.json` to record the Composer validation requirement.

## Verification performed in artifact environment

- PHP syntax check across `src`, `tests`, and `tools`.

## Not claimed

- No PHPUnit/PHPStan/runtime-green claim is made in the artifact environment because the local runtime `vendor/` and host application context are not available here.
