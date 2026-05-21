<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

/**
 * Backward-compatible data SQL facade for the temporary Attaching identifier migration.
 *
 * Row-map and copy statements live in narrower collaborators, while this class
 * preserves the stable public contract used by the migration facade/tests.
 */
final readonly class AttachmentIdentifierMigrationDataSql
{
    public function __construct(
        private AttachmentIdentifierMigrationMapSql $mapSql = new AttachmentIdentifierMigrationMapSql(),
        private AttachmentIdentifierMigrationCopySql $copySql = new AttachmentIdentifierMigrationCopySql(),
    ) {
    }

    public function createAttachmentIdMap(): string
    {
        return $this->mapSql->createAttachmentIdMap();
    }

    public function populateAttachmentIdMap(): string
    {
        return $this->mapSql->populateAttachmentIdMap();
    }

    public function createAttachmentLinkIdMap(): string
    {
        return $this->mapSql->createAttachmentLinkIdMap();
    }

    public function populateAttachmentLinkIdMap(): string
    {
        return $this->mapSql->populateAttachmentLinkIdMap();
    }

    public function copyAttachments(): string
    {
        return $this->copySql->copyAttachments();
    }

    public function copyAttachmentLinks(): string
    {
        return $this->copySql->copyAttachmentLinks();
    }
}
