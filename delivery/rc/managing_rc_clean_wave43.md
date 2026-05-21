# Managing RC-clean wave 43

## Active base

`Managing_rc_clean_wave42_cumulative.zip`

## Goal

Add an explicit owner-side runtime-proof assertion step after runtime proof capture. Wave42 captured and classified core/host logs; wave43 adds a deterministic pass/fail command that can be used as the final local gate.

## Changes

- Added `tools/runtime/assert-rc-runtime-proof.php`.
- The assertion command reads `delivery/rc/runtime-proof-results/runtime-proof-intake.json`.
- Core assertion exits successfully only when `core_runtime_green=true`.
- Host assertion can be required with `--require-host`, which additionally requires `host_integration_green=true`.
- Updated runtime proof README with assertion commands.
- Updated `delivery/rc/managing_rc_status.json` with assertion command contracts.

## Runtime proof status

No runtime-green claim is made in this artifact environment. The local owner repository remains the source of truth for PHPUnit, PHPStan, Symfony container, and host EasyAdmin route proof.

## Local commands

Core capture:

```powershell
powershell -ExecutionPolicy Bypass -File "tools/runtime/capture-rc-runtime-proof.ps1" -ProjectRoot "D:\PhpstormProjects\www\Managing"
```

Core assertion:

```powershell
php tools/runtime/assert-rc-runtime-proof.php
```

Core + host capture:

```powershell
powershell -ExecutionPolicy Bypass -File "tools/runtime/capture-rc-runtime-proof.ps1" -ProjectRoot "D:\PhpstormProjects\www\Managing" -HostRoot "D:\PhpstormProjects\www\app"
```

Core + host assertion:

```powershell
php tools/runtime/assert-rc-runtime-proof.php --require-host
```
