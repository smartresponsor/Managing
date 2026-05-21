# Managing RC-clean wave 12

Base: `Managing_rc_clean_wave11_cumulative.zip`.

## Goal

Continue RC-hardening by splitting the host path/PSR-4 discovery responsibilities. The previous `ManageHostPathResolver` still mixed path scanning with Composer/runtime PSR-4 autoload parsing.

## Changes

- Added `App\Managing\Service\Admin\Host\ManageHostPsr4RootResolver`.
- Moved Composer `autoload.psr-4` parsing and runtime `vendor/composer/autoload_psr4.php` parsing out of `ManageHostPathResolver`.
- Reduced `ManageHostPathResolver` from about 222 lines in the earlier RC-clean state to 112 lines in this wave.
- Kept manual-instantiation compatibility: `ManageHostPathResolver` can still construct the PSR-4 resolver lazily when it is created outside the Symfony container.
- Added container wiring for `ManageHostPsr4RootResolver`.
- Added unit coverage for PSR-4 root resolution and excluded namespace handling.

## Validation

- `php -l` passed for all PHP files under `src`, `tests`, and `tools`.
- PHPUnit/PHPStan were not executed in this archive environment because runtime vendor dependencies are not available.

## Remaining RC debt

- `AbstractManageContentCrudController` is still the largest class and remains a candidate for a final thin-controller pass.
- `ManageCrudResourcePolicy` and `Configuration` are policy/config-heavy by design but should be reviewed for documentation and schema clarity before RC seal.
