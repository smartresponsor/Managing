# Managing RC-clean wave 10

Base: `Managing_rc_clean_wave9_cumulative.zip`.

## Goal

Reduce the remaining special-case attachment identifier migration surface by separating transactional orchestration, SQL contract, and file-backed completion marker.

## Changed

- `AttachmentIdentifierMigrationService` is now a narrow orchestrator.
- Added `AttachmentIdentifierMigrationSql` to hold schema, copy, reseed, and information-schema SQL.
- Added `AttachmentIdentifierMigrationMarker` to isolate marker-file read/write behavior.
- Added `AttachmentIdentifierMigrationSupportTest` for the marker and SQL contract.

## Result

- `AttachmentIdentifierMigrationService` reduced from roughly 280 lines to 110 lines.
- The large SQL bodies no longer sit inside the transactional service flow.
- The one-off migration remains available for the generated Attaching CRUD bridge, but the code is easier to audit and remove later when the migration is no longer needed.

## Verification

- `php -l` was run over `src`, `tests`, and `tools` successfully.
- PHPUnit/PHPStan were not run because this archive does not include `vendor/`.
