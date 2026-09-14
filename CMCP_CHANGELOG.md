# CMCP execution journal

## 2026-09-13 — Managing RC hardening

### Reconnaissance baseline

- Read `AGENTS.md`, `README.md`, `composer.json`, repository documentation under `docs/`, current Git status/diff, representative source/tests, and the local `.gating/` surface.
- Read the required sibling contracts for Objecting, Cruding, Viewing, and Interfacing, plus Gating and the normative Canonization rules relevant to this task.
- Verified host development wiring in `../App/composer.json`: Managing and sibling components are consumed through Composer path repositories with `symlink: true`.
- Current branch: `checkpoint/managing-admin-crud-20260821`.
- Pre-existing dirty generated-controller changes are intentionally preserved and excluded from this RC patch wave.

### Canonization mapping consulted

- Canon007 — PSR-4 identity must remain literal.
- Canon008 — foreign component usage/dependency contracts must be explicit in Composer.
- Canon010 — architecture migrations must update runtime, tests, configuration, and documentation together.
- Canon017 — documentation must match the current runtime.
- Canon018 — `managing/manage` maps to `App\\Managing\\` and `Manage*` subject vocabulary.
- Canon019 — no Domain/Application/Infrastructure or Port/Adapter/Adaptor root taxonomy.
- Canon021 — Cruding owns generic application CRUD; Managing's EasyAdmin back-office CRUD is an explicit allowed exception.
- Canon023 — local component development dependencies use sibling path repositories with `symlink: true`.
- Canon024 — production Composer manifest is path-independent.
- Canon029 — PHP-CS-Fixer and PHPStan require repository-owned config and Composer execution scripts.
- Canon039 — PHPUnit requires repository-owned source/coverage configuration and ordinary/coverage scripts.

### RC-critical workstream

1. Restore reproducible package manifests and quality-tool execution contracts.
2. Fix deterministic policy/type/runtime regressions exposed by PHPUnit.
3. Reconcile obsolete tests/compatibility surfaces with the current Managing ownership boundary.
4. Reduce remaining RC architecture/test failures, then rerun Composer validation, PHPStan, PHPUnit and Gating.

### Growth workstream (post-RC)

- Richer review/history UX, operator-oriented audit presentation, and additional field/workflow ergonomics after correctness and package reproducibility are green.

## 2026-09-14 — RC closure pass

### Material implementation

- Declared the mandatory Objecting, Cruding, Viewing, and Interfacing package contour explicitly and added canonical local path/symlink repositories for development.
- Added the hard `administering/administration` dependency after confirming Managing directly type-hints Administering entity/service contracts; kept Rolling optional where the Managing documentation requires runtime-optional integration.
- Added path-independent `composer.prod.json`; synchronized Symfony 8.1-compatible dependencies and raised EasyAdmin/UX LiveComponent minimums above audited vulnerable ranges.
- Fixed policy alias normalization, field-type-policy propagation, Symfony 8 DI assertions, EasyAdmin generic typing, Doctrine entity metadata, and several narrowed/runtime type contracts.
- Retired stale Managing-owned attachment migration test assumptions and kept only a generated-controller compatibility no-op boundary.
- Converted generated host CRUD assertions to host-conditional integration coverage instead of broadening the Managing package dependency surface.
- Reused focused controller runtime/surface traits, split configuration facade concerns, extracted entity accessors and field-profile parsing, and restored the 180-line RC architecture ceiling without weakening the gate.
- Made Rolling-backed mutation review truly runtime-optional and returned a neutral safe review payload from the Managing public result; Symfony wiring uses an optional Rolling service reference.
- Materialized tracked PHPStan/PHP-CS-Fixer/PHPUnit execution contracts. Generated host bridge controllers are excluded from package-local static analysis/formatting and remain owned by generation/host integration.

### Verification

- `composer validate --strict`: PASS.
- `composer audit`: PASS, no security vulnerability advisories.
- `composer run-script cs:check`: PASS using tracked `.php-cs-fixer.dist.php`, 0 fixable files.
- `composer run-script phpstan`: PASS, 0 errors at level 6 on package-owned runtime.
- PHPUnit: PASS, 147 tests / 460 assertions / 8 host-conditional skips.
- `composer run-script test:coverage`: PASS under Xdebug 3.5.1 with persistent `var/coverage-summary.txt` output.
- PHP syntax lint on changed PHP files: PASS.
- Console RC validator: `rc_diagnostic_green`, zero blockers, zero Canon issues.

### Integration notes and residuals

- Pre-existing dirty `src/Controller/Crud/Generated/*` changes and untracked `Generated/CurrencingCrudController.php` were deliberately not modified or selected for integration.
- The untracked embedded `.gating/` tree is not Managing product source and remains excluded from integration.
- Shared host `../App` cannot currently reach Symfony command discovery because `App\\Facting\\FactingBundle` is enabled for `prod` but unavailable. This failure occurs before Managing/Gating execution and is recorded as an external host-runtime issue, not a Managing RC blocker.
- Post-RC growth remains richer review/history UX, operator-oriented audit presentation, and additional field/workflow ergonomics.
