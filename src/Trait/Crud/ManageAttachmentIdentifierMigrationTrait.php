<?php

declare(strict_types=1);

namespace App\Managing\Trait\Crud;

/**
 * Compatibility bridge for controllers generated before Managing stopped owning
 * the Attaching identifier migration.
 *
 * Managing is an administration/generation component. It must not execute
 * schema/data migrations for another component at request time. The actual
 * attachment identifier model is owned by Attaching/Objecting entity-first
 * contracts; this method remains as a harmless generated-controller BC hook.
 */
trait ManageAttachmentIdentifierMigrationTrait
{
    protected function migrateAttachmentIdentifierIfNeeded(): void
    {
        // Intentionally no-op: schema-first Attaching repair was retired from Managing.
    }
}
