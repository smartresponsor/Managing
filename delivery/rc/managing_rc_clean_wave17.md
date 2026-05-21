# Managing RC Clean Wave 17

Base: `Managing_rc_clean_wave16_cumulative.zip`

## Goal

Continue RC-hardening without adding business features by splitting the remaining large temporary Attaching identifier migration SQL contract into narrow collaborators.

## Changes

- Added `AttachmentIdentifierMigrationDataSql` for temporary ID maps and data-copy statements.
- Added `AttachmentIdentifierMigrationSchemaSql` for table creation and sequence reseed statements.
- Added `AttachmentIdentifierMigrationMetadataSql` for information-schema probes.
- Kept `AttachmentIdentifierMigrationSql` as the stable facade used by `AttachmentIdentifierMigrationService`.
- Added `AttachmentIdentifierMigrationSqlSplitTest` to lock the delegation contract.

## Result

`AttachmentIdentifierMigrationSql` is no longer a 200+ line SQL bag. The public API remains unchanged, but schema, data-copy and metadata concerns are separately reviewable and testable.

## Validation

- `php -l` over `src`, `tests`, and `tools`: PASS.
- PHPUnit/PHPStan were not executed in this archive environment because runtime `vendor/` is not present.
