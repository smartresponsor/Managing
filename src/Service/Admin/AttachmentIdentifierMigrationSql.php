<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

/**
 * Facade SQL contract for the temporary Attaching identifier migration.
 *
 * The public API remains stable for AttachmentIdentifierMigrationService, while
 * map/copy statements, table schema statements and metadata probes live in
 * narrow collaborators.
 */
final readonly class AttachmentIdentifierMigrationSql
{
    public function __construct(
        private AttachmentIdentifierMigrationDataSql $dataSql = new AttachmentIdentifierMigrationDataSql(),
        private AttachmentIdentifierMigrationSchemaSql $schemaSql = new AttachmentIdentifierMigrationSchemaSql(),
        private AttachmentIdentifierMigrationMetadataSql $metadataSql = new AttachmentIdentifierMigrationMetadataSql(),
    ) {
    }

    public function createAttachmentIdMap(): string
    {
        return $this->dataSql->createAttachmentIdMap();
    }

    public function populateAttachmentIdMap(): string
    {
        return $this->dataSql->populateAttachmentIdMap();
    }

    public function createAttachmentLinkIdMap(): string
    {
        return $this->dataSql->createAttachmentLinkIdMap();
    }

    public function populateAttachmentLinkIdMap(): string
    {
        return $this->dataSql->populateAttachmentLinkIdMap();
    }

    public function copyAttachments(): string
    {
        return $this->dataSql->copyAttachments();
    }

    public function copyAttachmentLinks(): string
    {
        return $this->dataSql->copyAttachmentLinks();
    }

    public function createAttachmentTable(): string
    {
        return $this->schemaSql->createAttachmentTable();
    }

    public function createAttachmentLinkTable(): string
    {
        return $this->schemaSql->createAttachmentLinkTable();
    }

    public function reseedAttachmentIdentity(): string
    {
        return $this->schemaSql->reseedAttachmentIdentity();
    }

    public function reseedAttachmentLinkIdentity(): string
    {
        return $this->schemaSql->reseedAttachmentLinkIdentity();
    }

    public function attachmentIdDataType(): string
    {
        return $this->metadataSql->attachmentIdDataType();
    }

    public function attachmentLinkIdDataType(): string
    {
        return $this->metadataSql->attachmentLinkIdDataType();
    }
}
