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
