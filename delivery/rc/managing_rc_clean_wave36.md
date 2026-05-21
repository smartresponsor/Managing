# Managing RC-clean wave 36

## Goal

Add an RC-clean architecture guard after the major refactoring sequence from waves 1-35.

This wave intentionally does not add new business behavior. It converts the closing RC criteria into executable architecture checks so future changes do not silently reintroduce the same god-object, hardcode, and neighbor-component coupling problems that triggered the cleanup track.

## Changes

- Added `tests/Architecture/ManageRcCleanArchitectureTest.php`.
- Added a production class line-budget gate of 180 lines.
- Added a generic-layer hardcode guard that forbids neighbor component FQCNs and application-role constants outside generated CRUD controllers.
- Added a closing-wave RC report existence guard for waves 33-36.

## Current scan result

The current production source tree passes the new line-budget threshold at the time of this wave. The largest production classes remain below the 180-line RC-clean ceiling.

Generated CRUD controllers remain allowed to point to host application entities, because their purpose is to bridge Managing to the discovered host runtime. The generic Managing services/controllers remain the protected area.

## Runtime proof

Only PHP syntax validation was run in this environment because the archive does not include runtime vendor dependencies.

The owner should run PHPUnit/PHPStan in the local repository after applying the touched-files archive.
