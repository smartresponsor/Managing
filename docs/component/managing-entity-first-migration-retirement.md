# Managing entity-first migration retirement

## Scope

This pass retires the schema/data migration code that was still embedded inside
`Managing` for the Attaching attachment identifier repair.

## Decision

`Managing` is not the owner of attachment persistence. It owns CRUD/admin
configuration, field visibility/profile storage, controller generation and admin
runtime integration. Therefore it must not carry SQL table creation, temporary
row-map tables, data-copy statements or PostgreSQL-only migration orchestration
for `attachment` / `attachment_link`.

## Retired schema-first source

- `Managing/src/Migration/**`

## Entity-first status

The current Managing-owned persistent model remains:

- `App\Managing\Entity\Crud\ManageCrudFieldViewProfileRule`

It now explicitly points to its Doctrine repository class:

- `App\Managing\Repository\Crud\ManageDoctrineCrudFieldViewProfileRuleRepository`

## Compatibility

The generated-controller compatibility trait remains, but its
`migrateAttachmentIdentifierIfNeeded()` hook is now an intentional no-op. This
prevents hard failures in already-generated controllers while removing request
runtime schema migration behavior from Managing.

## Objecting

No Objecting embeddable traits were added in this pass. `createdAt` and
`updatedAt` in `ManageCrudFieldViewProfileRule` are profile-storage lifecycle
facts in the existing Managing model; changing them to Objecting embeddables
would be a schema change rather than migration retirement.

## Legacy monolith

`Entity-src(6).zip` does not contain an older Managing aggregate model with
additional relations to restore. The old SQL here was cross-component Attaching
repair code, not a missing Managing entity.
