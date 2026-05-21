# Managing RC Clean Wave 41

## Goal

Move from architecture-clean seal into post-RC runtime-proof accuracy.

Wave 41 does not introduce new business behavior. It tightens the runtime proof intake so skipped PHPUnit/PHPStan checks are not accidentally reported as merely captured, and runtime-green is computed only from explicit pass statuses.

## Changes

- Updated `tools/runtime/write-rc-runtime-proof-intake.php`.
- Added explicit log status classification:
  - `pass`
  - `failed`
  - `skipped`
  - `missing`
  - `captured_review_required`
- Added `overall_status` and `runtime_green` fields to `runtime-proof-intake.json`.
- Updated `delivery/rc/runtime-proof-results/README.md` with the runtime proof contract.

## Proof in artifact environment

- PHP syntax check over `src`, `tests`, and `tools`: PASS.
- PHPUnit/PHPStan were not run in the artifact environment because the archive does not include local `vendor/`.

## Owner-side next step

Run:

```powershell
powershell -ExecutionPolicy Bypass -File "tools/runtime/capture-rc-runtime-proof.ps1" -ProjectRoot "D:\PhpstormProjects\www\Managing"
```

Review `delivery/rc/runtime-proof-results/runtime-proof-intake.md` before making any runtime-green claim.
