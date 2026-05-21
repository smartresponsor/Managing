# Managing RC Runtime Proof Results

This directory is intentionally used for owner-side runtime proof artifacts.

Run core component proof from the Managing repository root:

```powershell
powershell -ExecutionPolicy Bypass -File "tools/runtime/capture-rc-runtime-proof.ps1" -ProjectRoot "D:\PhpstormProjects\www\Managing"
```

Run component proof plus host-application integration proof when a host app is available:

```powershell
powershell -ExecutionPolicy Bypass -File "tools/runtime/capture-rc-runtime-proof.ps1" -ProjectRoot "D:\PhpstormProjects\www\Managing" -HostRoot "D:\PhpstormProjects\www\app"
```


After capture, assert the core runtime proof from the repository root:

```powershell
php tools/runtime/assert-rc-runtime-proof.php
```

When host integration is part of the RC gate, assert the host proof as well:

```powershell
php tools/runtime/assert-rc-runtime-proof.php --require-host
```

Expected core outputs:

- `syntax.log`
- `canon-guard.log`
- `composer-validate.log`
- `powershell-compatibility.log`
- `phpunit.log`
- `phpstan.log`
- `runtime-proof-intake.json`
- `runtime-proof-intake.md`

Expected host-integration outputs when `-HostRoot` is provided:

- `host-container.log`
- `host-easyadmin-routes.log`

The intake writer classifies each check as one of:

- `pass`
- `failed`
- `skipped`
- `missing`
- `captured_review_required`

`core_runtime_green` is true only when syntax, canon guard, Composer validation, PowerShell compatibility, PHPUnit, and PHPStan all classify as `pass`.

`host_integration_green` is true only when the host Symfony console proof and Manage/EasyAdmin route-surface proof both classify as `pass`.

`runtime_green` remains the core component runtime signal. Host-application integration is reported separately because host boot and route registration depend on the consuming application.

Composer validation is part of the core runtime gate. If the `composer` command is unavailable, `composer-validate.log` is classified as `skipped`, and core runtime proof remains non-green until Composer validation is captured.

PowerShell compatibility proof intentionally checks the Windows PowerShell-safe path-handling baseline. Apply scripts and local helper scripts must avoid string-based `.TrimEnd('\\')` and must not require `[System.IO.Path]::GetRelativePath()` on older Windows PowerShell runtimes.
