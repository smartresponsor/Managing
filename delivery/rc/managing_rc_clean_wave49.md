# Managing runtime-fix wave 49

Base: `Managing_rc_clean_wave48_cumulative.zip`.

## Purpose

Fix PHPStan runtime-proof executable discovery on Windows after `composer require --dev phpstan/phpstan` still produced `phpstan: skipped` in the runtime intake.

## Changes

- Added robust PHPStan executable resolver to `tools/runtime/capture-rc-runtime-proof.ps1`.
- Checked candidates now include:
  - `vendor/bin/phpstan.bat`
  - `vendor/bin/phpstan`
  - `vendor/bin/phpstan.php`
  - `vendor/phpstan/phpstan/phpstan`
  - `vendor/phpstan/phpstan/phpstan.phar`
- When PHPStan is missing, `phpstan.log` now records all checked paths and matching `vendor/bin/*phpstan*` files.
- When PHPStan is found, `phpstan.log` records the executable path before running analysis.

## Runtime status

This wave does not claim runtime green. It only fixes the discovery layer so the next owner-side capture can distinguish real PHPStan errors from executable lookup failure.
