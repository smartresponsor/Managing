# Managing RC-clean wave 34

Base: `Managing_rc_clean_wave33_cumulative.zip`.

## Goal

Split the remaining broad data-copy SQL seam used by the temporary Attaching identifier migration.

## Changes

- Added `AttachmentIdentifierMigrationMapSql` for temporary row-map table creation/population SQL.
- Added `AttachmentIdentifierMigrationCopySql` for attachment and attachment-link data-copy SQL.
- Kept `AttachmentIdentifierMigrationDataSql` as a backward-compatible facade.
- Updated `AttachmentIdentifierMigrationSqlSplitTest` to cover the new map/copy split.

## Verification

- `php -l` was run across `src`, `tests`, and `tools`.
- PHPUnit/PHPStan were not run because the archive does not include runtime `vendor/`.
