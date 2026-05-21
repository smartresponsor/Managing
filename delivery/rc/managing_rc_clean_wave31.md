# Managing RC-clean wave 31

Base: `Managing_rc_clean_wave30_cumulative.zip`

## Goal

Reduce the remaining DependencyInjection configuration-tree boilerplate without changing the public `managing` configuration schema.

## Changes

- Added `src/DependencyInjection/ManagingConfigurationNodeBuilder.php`.
- Moved repetitive Symfony Config Definition node grammar into this helper:
  - scalar list nodes;
  - scalar maps;
  - scalar-list maps;
  - nested integer maps;
  - nested scalar maps;
  - boolean nodes;
  - scalar nodes.
- Reworked `src/DependencyInjection/Configuration.php` to declare configuration nodes as grouped schema arrays.
- Preserved all existing public configuration keys and defaults.
- Added `tests/Unit/DependencyInjection/ManagingConfigurationNodeBuilderTest.php`.

## Size effect

- `Configuration.php`: about 177 lines -> about 134 lines.
- New helper keeps TreeBuilder grammar isolated from the configuration schema list.

## Verification

- `php -l` passed for `src`, `tests`, and `tools`: 131 PHP files.
- PHPUnit/PHPStan were not executed because this archive does not include runtime `vendor/`.
