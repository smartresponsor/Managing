# Managing RC-clean wave 30

Base: `Managing_rc_clean_wave29_cumulative.zip`.

## Goal

Split host class-name extraction/formatting seams so host discovery and generated-controller selection do not keep PHP token parsing and naming transformations inside higher-level services.

## Changes

- Added `ManagePhpClassNameExtractor` for PHP token parsing and class FQCN extraction.
- Added `ManageClassNameFormatter` for shared short-class, studly, slug, humanize, and resource-short-name normalization.
- Reduced `ManageHostClassNameResolver` to host component/resource resolution orchestration.
- Reused `ManageClassNameFormatter` inside `ManageCrudControllerGenerator` to remove duplicate naming helpers.
- Added `ManageHostClassNameFormattingSplitTest` for the extracted formatter/extractor contracts.

## Validation

- `php -l` passed for `src`, `tests`, and `tools`.
- Runtime PHPUnit/PHPStan were not executed because the archive does not include runtime `vendor/`.
