# CMCP Orchestration Journal

## repository-implementation-managing — 2026-09-17

### Baseline and reconnaissance

- Requested workspace: `D:\PhpstormProjects\www\Managing`.
- Available factual write baseline in this execution context: GitHub `master` at `97eaeddfae481fb8eaafc381fd9f3dee3ef0766b` (tree `e72ca5e02d325f4f273a63d37d199f7095f8fa8d`). The local Windows worktree, sibling symlinks, uncommitted files, and local Composer resolution are not observable from this execution context and were not inferred.
- Read target `AGENTS.md`, `README.md`, `README.adoc`, `composer.json`, source/test topology, Symfony service wiring, current RC delivery notes, and representative administration/CRUD code and tests.
- Read the required component contracts for Objecting, Cruding, Viewing, and Interfacing and treated Gating as executable enforcement rather than a product dependency.
- Read Canonization textual architecture rules relevant to this pass, including Canon008 (Composer dependency integrity), Canon019 (no alternative layer taxonomy), Canon020 (typed Symfony role roots), and Canon021 (Cruding owns generic CRUD). Canon021/Gating explicitly exempt EasyAdmin CRUD controllers, so Managing's EasyAdmin controller hierarchy was not mechanically migrated to Cruding.
- No `MANIFEST.json` was present in the observed Managing root.

### Market and boundary baseline

- Mature administration/CMS surfaces fail closed around authorization mutation application: malformed review metadata must not become trusted simply because surrounding permission/scope fields look plausible.
- Managing owns its CMS/admin presentation and field-access policy semantics. Administering owns review/apply control-plane records; Cruding owns platform generic CRUD outside the explicit EasyAdmin exception; Viewing/Interfacing own their presentation/shell contracts; Objecting owns reusable system fields.
- Growth work remains separate: richer management UX, analytics, bulk workflows, and additional admin automation do not block this RC security hardening pass.

### Canon mapping

- Canon008: the current `composer.json` does not yet express all observed/required first-party component dependencies. In particular, source imports `App\\Administering\\...` while `administering/*` is not declared, and the requested Objecting/Cruding/Viewing/Interfacing contour is also absent. This remains an explicit RC packaging tail because modifying `composer.json` without resolving and updating `composer.lock` would leave a knowingly inconsistent package state.
- Canon019: no new `Domain`, `Application`, `Infrastructure`, `Port`, `Adapter`, or `Adaptor` root was introduced.
- Canon020: the new review checker is placed in the existing technical-role root `src/Validator/Administration` and its regression test mirrors the repository's current test topology.
- Canon021: EasyAdmin `AbstractCrudController` usage is not treated as a generic-CRUD violation because the canonical guard matrix explicitly exempts EasyAdmin CRUD.

### RC-critical implementation

- Identified a fail-open branch in `ManagingFieldAccessMutationApplyService`: a non-array `safe_context` caused `isManagingFieldAccessReview()` to return `true`.
- Added `ManagingFieldAccessReviewValidator` as a narrow Managing-owned validator for review metadata.
- Changed malformed `safe_context`, malformed `target`, malformed `surface`, foreign explicit target components, wrong scope, and unsupported mutation types to fail closed.
- Preserved the existing accepted paths: explicit Managing target, canonical Managing review surface, and recognized Managing policy keys when optional safe context is absent.
- Delegated apply-service review classification to the validator before the Administering apply service can be called.
- Added regression coverage for eight acceptance/rejection scenarios.
- No public interface, route, Doctrine mapping, Entity, migration, or navigation surface changed.

### Verification and integration

- Search confirmed no repository call-site manually constructs `ManagingFieldAccessMutationApplyService`; Symfony autowiring covers the new validator through the existing `App\\Managing\\` resource.
- The three changed PHP surfaces passed PHP 8.4 syntax checks in the available execution environment.
- GitHub diff review confirmed exactly four changed files: this journal, the apply service, the new validator, and its regression test.
- RC branch `rc/managing-fail-closed-review-20260917` was based exactly on `master` `97eaeddfae481fb8eaafc381fd9f3dee3ef0766b`, remained zero commits behind, and was published through PR #3.
- PR #3 became mergeable with no conflicts. GitHub exposed no commit status checks for the PR head, so no CI result was inferred.
- PR #3 was squash-merged to `master` as `7dd1a5ea253cf67dac510d292967aea3936b5bed`.
- A container clone attempt from this execution environment failed because outbound DNS for `github.com` is unavailable; no runtime gate result is fabricated from that failure.
- Local executable gates that remain required before final RC seal: Composer validate/check-lock, PHPStan, PHPUnit, Symfony container/YAML lint, Doctrine validation where applicable, and Gating against the actual `D:\PhpstormProjects\www\Managing` workspace.

### Residual RC tail

- Canon008/package integrity remains open: the current Managing Composer manifest does not yet declare the observed `App\\Administering\\...` dependency or the requested Objecting/Cruding/Viewing/Interfacing contour.
- This execution deliberately did not modify `composer.json` without a corresponding dependency resolution and `composer.lock` update. Closing this tail requires the actual local sibling/path-repository environment or another Composer-capable environment with access to those packages.

### Growth work kept outside RC

- Richer EasyAdmin management workflows and bulk operations.
- Additional management observability/analytics.
- Broader UX/provider refinements beyond the authorization review safety boundary.

## repository-implementation-managing — packaging contour pass — 2026-09-17

### Updated baseline

- Started from current `master` `3eaa2450d19a06599e9edf22c49c9f1b04e86a8a`, which includes the merged fail-closed ACL review hardening and post-integration journal update.
- Re-read current `composer.json`, current source references to foreign `App\\...` namespaces, and the package manifests for Administering and Rolling.
- Confirmed direct/runtime-facing dependencies beyond the previously requested helper contour: `administering/administration` supplies the concrete review record and apply-service interface used by Managing; `rolling/role` supplies configured/runtime field-access contracts referenced by Managing.

### Packaging implementation

- Added the explicit first-party requirements `administering/administration`, `rolling/role`, `objecting/object`, `cruding/crud`, `viewing/view`, and `interfacing/interface`, all at `dev-master` for the development workspace.
- Added sibling Composer `path` repositories for `../Administering`, `../Rolling`, `../Objecting`, `../Cruding`, `../Viewing`, and `../Interfacing`, each with `symlink: true`.
- Added `tools/qa/managing-first-party-dependency-contour.php` and Composer script `verify:first-party-dependencies` so the six-package development contour cannot silently regress.
- The guard itself passed PHP 8.4 syntax validation in the available execution environment.
- Added `tools/qa/close-managing-composer-contour.ps1` as the reproducible local closure runner. It requires all six sibling repositories, resolves only the six first-party packages with dependencies, validates Composer/lock consistency, runs the contour guard, PHP lint, PHPStan, PHPUnit, optional bundle-local Symfony/Doctrine console gates, and mandatory sibling Gating. Missing PHPStan/PHPUnit/Gating is a hard failure rather than a silent skip.

### Integration gate

- `composer.lock` on the branch is intentionally still the pre-contour lock (`content-hash` from the old manifest). Therefore this branch is not merge-ready yet and must not be presented as Composer-green.
- Required local closure step: from `D:\PhpstormProjects\www\Managing`, run `powershell -ExecutionPolicy Bypass -File .\tools\qa\close-managing-composer-contour.ps1` with sibling repositories present.
- The runner performs the bounded dependency resolution and hard local gates. If the bundle has no local `bin\console`, host/container Symfony gates remain a separate acceptance step and are reported explicitly rather than silently treated as executed.
- After the runner passes, inspect the resulting `composer.lock` diff, host/container Symfony composition, and Gating output before promoting the draft PR to merge-ready.
- No direct edit of generated `composer.lock` data was attempted because hand-authoring path-package lock entries and transitive dependency metadata would be non-reproducible and unsafe.

## 2026-09-18 — RC hardening integration pass

### What was integrated

- Selectively integrated the prior RC-hardening commit `703c3e0a68ff417ccb500e207d7fca004d3966d3` onto the current composer-contour branch.
- Preserved the current fail-closed Managing field-access apply validator and the current first-party dependency contour.
- Deliberately excluded the old hardening `composer.json`, `composer.lock`, and old journal snapshot to avoid regressing the resolved packaging work.
- Integrated repository-owned QA configuration (`phpstan.neon`, `.php-cs-fixer.dist.php`, updated `phpunit.xml.dist`), RC class splits, policy/type corrections, host-conditional generated CRUD tests, and Symfony DI test compatibility.
- Aligned `composer.prod.json` with the current direct runtime contour, including `rolling/role`, while keeping production configuration free of local path repositories.

### Closure runner corrections

- PHPStan now runs through tracked `phpstan.neon` against package-owned runtime instead of imposing an ad-hoc level-8 scan across tests/tools.
- Gating now uses the canonical sibling `../Gating/.gating` policy root rather than requiring a repository-local Managing severity profile.
- Added explicit `composer.prod.json` validation.
- Corrected the PowerShell Gating block so no literal escape sequence remains in executable code.

### Current verification state

- Last pre-integration diagnostic run had Composer update/validate, dependency contour, and PHP syntax green.
- That run's PHPStan/PHPUnit/Gating failures are superseded by this integration because the failing code/config surfaces were materially changed afterward.
- PR #4 remains mergeable and draft pending a fresh full closure run on the integrated head plus host/container Symfony acceptance where available.
- No GitHub commit-status checks are currently reported for the PR head.

### Remaining RC gates

1. Fresh closure runner execution against the integrated branch.
2. Repair only findings that reproduce after the integrated hardening/configuration changes.
3. Host/container Symfony composition acceptance if the bundle-local console remains unavailable.
4. Final PR diff/worktree/head review, then promote and merge only if green.

## 2026-09-19 — local RC closure verification

### Local baseline and WIP preservation

- Revalidated the authoritative local workspace on branch `rc/managing-composer-contour-verify-20260918`, tracking `origin/rc/managing-composer-contour-20260917` at `216414a4bf55ca1153fb5d11a6f190721d332437` before this pass.
- Existing generated CRUD WIP (`Currencing`, `Ordering`, `Paying`, `Shipping`, `Walleting`, plus current `Attaching/Cataloging/Paging` edits) remains explicitly outside this RC contour and is not staged or rewritten.
- Repository-local transient `.console-mcp/` and `.gating/` artifacts are also excluded from RC staging.

### Managing-owned fixes and verification

- Removed stale PHPStan suppressions and redundant dynamic type guards now that the Rolling contracts resolve through the declared first-party dependency contour.
- Extended all nine local first-party path repositories to publish explicit `dev-master` path versions, satisfying Canon043 consistently for direct and transitive path packages.
- Strengthened `tools/qa/managing-first-party-dependency-contour.php` so every expected local path repository must expose its exact `dev-master` package version.
- Fresh local verification passed Composer update/strict lock validation, production Composer validation, first-party contour guard, PHP syntax checks, PHPStan, PHPUnit, and CS check. PHPUnit result: 155 tests, 469 assertions, 8 skipped.

### Gating configuration blocker

- The canonical sibling Gating policy root has component profiles for several components but currently has no `.gating/profile/component/managing.yaml`.
- Gating profile discovery searches the target repository for a local component profile; Managing intentionally does not carry a fake local `.gating` policy copy. With no explicit Managing profile, the runner falls back to the broad registry and defaults the namespace to `App`, producing findings that are not valid Managing component-profile evaluation.
- Fresh full closure re-run confirms Canon043 now passes. The no-profile Gating invocation still reports 10 failures from the broad registry; these are classified as a Gating configuration/invocation blocker outside the Managing responsibility boundary. The Gating repository is not modified by this RC task.

### Host acceptance blocker

- The host `App` workspace remains independently dirty and currently enables `App\\Facting\\FactingBundle` for all environments; existing local host logs/journal record the unavailable Facting bundle boot failure. Managing does not mutate that host state.
- Host/container Symfony acceptance therefore remains externally blocked and cannot be attributed to the Managing package changes until the host composition is repaired.

## 2026-09-22 — compact canonical debt closure

### Baseline
- Fresh Gating baseline after guard corrections left real hard debt in Canon004, Canon006, Canon018, Canon025, Canon030, Canon038, Canon039, and Canon053.
- This bounded pass intentionally closes only Canon004/006/038/039 before the larger runtime/topology and subject-prefix migrations.

### Material implementation
- Renamed the persisted profile-rule class to `ManageCrudFieldViewProfileRuleEntity` and synchronized runtime/tests/docs.
- Moved `ManagingConfigurationNodeBuilder` from DependencyInjection to `Builder/DependencyInjection` and renamed it `ManageConfigurationNodeBuilder`.
- Renamed `config/packages/managing.yaml` to `config/packages/manage_runtime.yaml`.
- Enabled PHPUnit path/branch coverage in the persistent `test:coverage` script.

### Verification
- `composer validate --strict --check-lock`: PASS.
- PHP-CS-Fixer: PASS.
- PHPStan: PASS, 0 errors.
- PHPUnit: PASS, 155 tests / 469 assertions / 8 skipped.
- Canon004, Canon006, Canon038, and Canon039: PASS.
- Remaining hard debt is intentionally scoped to Canon018, Canon025, Canon030, and Canon053.

## 2026-09-23 — RC implementation continuation

### Baseline and reconnaissance
- Authoritative local workspace: `D:\PhpstormProjects\www\Managing`.
- Current branch/head at reconnaissance: `rc/managing-attaching-entity-suffix-publish-20260921` @ `32628658bdc34bfb8d4ae0f09c59481b6341dedf`, tracking `origin/rc/managing-attaching-entity-suffix-publish-20260921`, four commits ahead and zero behind.
- Pre-existing dirty worktree contained exactly `.gating/README.md` and `composer.json`; neither was discarded.
- Read current Managing `AGENTS.md`, `README.md`, `composer.json`, existing CMCP journal, mandatory helper contracts for Objecting/Cruding/Viewing/Interfacing/Gating, and Canonization textual rules.
- Market/open-source baseline consulted EasyAdmin's current security/action contracts: server-side action/entity/field authorization and explicit validation of mutating batch actions are mature-admin expectations; UI hiding alone is not authorization.

### Target-to-canon mapping
- Canon018: `managing/manage` => component namespace `App\Managing\` and subject prefix `Manage*`; remaining findings must be resolved against that identity.
- Canon025: Managing is a reusable Symfony component and must retain standalone boot surfaces plus bundle integration.
- Canon030: because Doctrine ORM and migrations are present, Managing must expose and pass an executable schema-parity contract; current composer edits add the missing migrations/console runtime required by that contract.
- Canon053: only canonical helper symlink repositories are permitted. Current added Collectioning and Tabling paths are allowed exceptions; capability-to-capability symlinks remain prohibited.
- Gating boundary: consumer `.gating/` is generated artifact state only; normative Gating documentation/policy belongs to Gating, so the current `.gating/README.md` replacement requires correction rather than promotion as Managing-owned documentation.

### RC-critical workstream
- Close Composer manifest/lock parity first, then run Gating, CS, PHPStan, PHPUnit, dependency contour and Doctrine schema parity.
- Repair only reproduced Managing-owned hard failures; preserve unrelated existing branch work.

### Growth workstream
- Richer CMS analytics, bulk workflow UX, management automation and additional presentation refinements remain post-RC unless a gate proves they are required for correctness or operability.

### First reproduced gate
- `composer validate --strict --check-lock`: FAIL (exit 2) because `composer.lock` is stale and `symfony/panther` is missing from the lock file.


## 2026-09-24 — RC convergence and standalone persistence closure

### Canon and runtime repairs
- Completed Canon018 subject-prefix migration across runtime, generated CRUD controllers, services/interfaces/validators/values, tests, service wiring, and generator output; generated controllers now use the Manage* subject prefix permanently.
- Closed Canon047 by moving Doctrine manager/registry access behind repository contracts and repository implementations.
- Restored consumer .gating/ to artifact-only state per Canon052.
- Added standalone Symfony boot surfaces and canonical infra/SQLite Doctrine configuration with underscore_number_aware naming.
- Added the executable Doctrine migration chain for ManageCrudFieldViewProfileRuleEntity.
- Corrected the first-party dependency contour to permit the canonical Gating helper symlink while rejecting non-helper sibling symlinks.
- Added Playwright tooling and refreshed the npm lock.

### Final verification
- composer validate --strict --check-lock: PASS after dependency lock closure.
- Standalone bin/console about --env=test: PASS on Symfony 8.1.7 / PHP 8.4.13.
- composer verify:first-party-dependencies: PASS.
- composer schema:parity: PASS; migrations current, ORM mapping valid, database schema synchronized.
- composer cs:check: PASS.
- composer phpstan: PASS, 0 errors.
- composer test: PASS, 155 tests / 469 assertions / 8 skipped.
- composer test:coverage: PASS; current coverage evidence regenerated.
- npm test: PASS with the configured Playwright runner.
- Full Gating: PASS with 70 rules, 0 failed, 5 warnings, 13 skipped. Canon018/025/027/028/030/041/047/052/053/054 all PASS.

### Advisory debt outside hard RC closure
- Canon011 flags five silent-fallback candidates for later behavioral/contract review.
- Canon021 is advisory only; the flagged CRUD surfaces are EasyAdmin-specific and covered by the Canon021 exemption.
- Canon031 semantic PHPDoc coverage remains below the advisory threshold.
- Canon040 reports HIGH_TEST_DEBT: lines 47.5%, methods 42.6%, branches 66.4%; evidence is current, but coverage growth remains post-RC work.
- Canon042 behavioral/UI coverage evidence remains absent; Playwright tooling is installed and executable, dedicated UI scenarios remain post-RC growth work.
