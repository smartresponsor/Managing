# Managing RC Clean Wave 28

## Goal

Split deterministic generated CRUD controller PHP source rendering away from filesystem synchronization.

## Changes

- Added `ManageGeneratedCrudControllerSourceRenderer`.
- Kept `ManageGeneratedCrudControllerWriter` as the filesystem synchronization service.
- Moved generated controller source rendering, read-only method rendering, migration trait rendering, and generated class short-name logic into the renderer.
- Kept `ManageGeneratedCrudControllerWriter::controllerSource()` as a backward-compatible facade for existing tests and callers.
- Added service wiring for the renderer.
- Added renderer unit coverage.

## Compatibility

- Generated controller contract remains unchanged.
- Existing writer public API remains available.
- Manual construction remains supported via lazy/default renderer construction.

## Proof

- `php -l` was run across `src`, `tests`, and `tools`.
- PHPUnit/PHPStan were not run because this archive does not include runtime `vendor/`.
