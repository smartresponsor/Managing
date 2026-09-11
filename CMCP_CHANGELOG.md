# CMCP_CHANGELOG

## engine-20260911152412-managing-fed750

### Iteration 1 — reconnaissance and baseline

- Target: `Managing`; workspace authority is limited to this repository.
- Baseline HEAD: `97eaeddfae481fb8eaafc381fd9f3dee3ef0766b` (`master`).
- Read target `AGENTS.md`, `README.md`, `README.adoc`, `composer.json`, root/config/source inventory, existing RC delivery history, tests/tools surfaces available in the repository tree.
- Target role: Symfony/EasyAdmin content-management surface. It owns CMS/back-office behavior, field visibility, permissions and business-focused EasyAdmin screens; it is not a diagnostics/logging portal.
- Mandatory dependency contour consulted: Objecting (`objecting/object`), Cruding (`cruding/crud`), Viewing (`viewing/view`), Interfacing (`interfacing/interface`). Their package manifests confirm distinct ownership for object system fields, generic CRUD, view rendering, and shared interface/shell capabilities.
- Canonization consulted as read-only normative source: `Canon000ComponentPrefixRule.md`, `Canon001TechnicalRoleFirstRule.md`, `Canon002InterfaceTreeMirrorsImplementationRule.md`, `Canon022StandaloneApplicationDependencyBaselineRule.md`; Gating mirror `Canon022StandaloneApplicationDependencyBaselineRule.php` also inspected.
- Canon mapping: Managing's existing technical-role-first tree is preserved in this task; no speculative namespace/tree migration is authorized without a concrete failing mapping. EasyAdmin remains the Managing-specific CMS implementation surface. The execution specification, however, explicitly requires Objecting/Cruding/Viewing/Interfacing to be real application dependencies and their declarations to be verified in the target Composer manifest.
- Baseline defect selected for RC-critical work: `composer.json` declares EasyAdmin and Symfony/Doctrine runtime packages but omits all four mandatory SmartResponsor dependency-contour packages.
- RC-critical workstream: make the dependency boundary explicit in `composer.json`, keep the package surface coherent, and verify the resulting manifest/branch state.
- Growth workstream (not RC-blocking): deeper CMS UX parity such as richer ACL/field-policy diagnostics, persisted filters, preview workflows and admin observability; no speculative growth implementation in this run.
- Market/maturity baseline reviewed: mature Symfony admin surfaces expose explicit security/ACL, batch/custom actions, configurable fields/forms and reusable admin package boundaries. These reinforce Managing's current CMS responsibility without moving generic CRUD or system-field ownership into Managing.
- Risks: dependency constraints must match current sibling development packages; broad class/tree rewrites would exceed the evidence gathered and are intentionally excluded.
- Planned gates: Composer manifest structural validation, dependency declaration verification, targeted source/config inspection, branch diff review, and available GitHub CI/check status after integration.

### Что имеем?
Authoritative repository and sibling/canon sources are reachable; the concrete Composer dependency-boundary gap is identified.

### Что осталось?
Patch the manifest, verify no ownership drift, inspect resulting diff/checks, integrate only if the bounded RC change is green.

### Iteration 2 — material implementation

- Updated `composer.json` runtime `require` with `cruding/crud`, `viewing/view`, `interfacing/interface`, and `objecting/object` using the workspace development constraint `*@dev`.
- Added local Composer path repositories for `../Cruding`, `../Interfacing`, `../Objecting`, and `../Viewing`, matching the established umbrella-workspace symlink pattern used by sibling SmartResponsor components.
- No PHP namespaces, source trees, controllers, routes, Doctrine mappings, templates, or unrelated repositories were mutated.

### Iteration 3 — verification and fix

- Verified each declared package identity against the current sibling Composer manifests.
- Verified Canon022 requires the direct dependency contour for applicable standalone Symfony applications and Gating mirrors that rule.
- Local execution runtime has PHP 8.4 but no Composer binary and cannot resolve `github.com` DNS, so it cannot safely run the Composer solver.
- Added a temporary GitHub Actions RC workflow to obtain a networked Composer environment. Run `34631332110` failed before any step and without a runner (`runner_id: 0`, `steps: []`).
- Reworked the workflow to remove all third-party Actions and use only shell, Git, Docker, official `composer:2`, and `php:8.4-cli`. Run `34631451039` failed identically before any step with no runner assigned (`runner_id: 0`, `steps: []`). This proves the blocker is hosted-runner availability/policy rather than Composer or workflow implementation.
- `composer.lock` therefore remains solver-unsynchronized. No manual lock fabrication was attempted.

### Iteration 4 — debt closure and integration

- Created branch `cmcp/engine-20260911152412-managing-fed750` from the authoritative baseline.
- Opened PR #2 (`RC: declare Managing platform dependency contour`).
- PR remains Git-mergeable but intentionally unmerged because the Composer lock/install gate is not green.
- Removed the temporary failing RC workflow after runner unavailability was confirmed, leaving no permanent workflow debt.
- No speculative growth work or cross-repository mutation was introduced.

### Iteration 5 — final acceptance and handoff

- Post-integration state inspected: `master` remains unchanged; task mutations remain isolated to the RC branch/PR.
- Accepted as materially implemented but not release-accepted: dependency ownership and local path wiring are corrected in the proposed manifest, while solver-generated `composer.lock` and executable quality-gate evidence are still required before merge.
- Required next gate in the real workspace or another runner-enabled environment: run Composer update for the four sibling packages, then `composer validate --strict`, PHP syntax, PHPStan, PHPUnit, and Gating. Merge PR #2 only after those gates are green.

### Что достигнуто?
The concrete dependency-boundary defect and workspace path wiring are patched and reviewable on an isolated branch. Two independent attempts to execute the remaining solver/gates through GitHub Actions confirmed an infrastructure-level runner blocker rather than a repository-level failure.

### Что осталось до RC?
Obtain one Composer-capable execution environment with sibling path repositories available, regenerate and verify `composer.lock`, run the repository quality gates, and merge PR #2 only after those checks pass.
