<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud;

use App\Managing\Service\Admin\AttachmentIdentifierMigrationService;

/**
 * Small generated-controller bridge for the legacy Attaching identifier repair.
 *
 * Keeping this code in a trait prevents the CRUD generator from embedding the
 * full migration bootstrap into generated controllers while preserving the
 * existing no-constructor controller contract.
 */
trait ManageAttachmentIdentifierMigrationTrait
{
    protected function migrateAttachmentIdentifierIfNeeded(): void
    {
        $doctrine = $this->container->get('doctrine');
        $cacheDir = (string) $this->container->get('parameter_bag')->get('kernel.cache_dir');
        $connection = $doctrine->getConnection();
        $markerFile = $cacheDir.'/attaching_migration_complete.flag';

        (new AttachmentIdentifierMigrationService($connection, $markerFile))->migrateIfNeeded();
    }
}
