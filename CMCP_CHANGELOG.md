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
