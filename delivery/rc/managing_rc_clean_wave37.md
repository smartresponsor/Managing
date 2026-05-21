# Managing RC-clean wave 37

## Goal

Tighten dependency-injection/config consistency after the architecture guard wave.

## Changes

- Added `ManagingParameterLoader` as the single place that maps processed Managing config keys to container parameters.
- Reduced `ManagingExtension` parameter-boilerplate and kept the bundle compile-time CRUD synchronization flow unchanged.
- Preserved all existing public `managing.*` parameter names.
- Added unit coverage for critical parameter keys and loading behavior.

## Verification

- PHP syntax check passed for `src`, `tests`, and `tools`.
- Runtime PHPUnit/PHPStan were not executed in this archive context because runtime `vendor/` is not included.

## RC status

This wave supports the RC wiring/DI pass by preventing parameter drift between `Configuration`, `ManagingExtension`, and `services.yaml`.
