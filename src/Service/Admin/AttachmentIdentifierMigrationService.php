<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

final readonly class AttachmentIdentifierMigrationService
{
    private AttachmentIdentifierMigrationSql $sql;
    private AttachmentIdentifierMigrationMarker $marker;

    public function __construct(
        private Connection $connection,
        ?string $cacheMarkerFile = null,
        ?AttachmentIdentifierMigrationSql $sql = null,
        ?AttachmentIdentifierMigrationMarker $marker = null,
    ) {
        $this->sql = $sql ?? new AttachmentIdentifierMigrationSql();
        $this->marker = $marker ?? new AttachmentIdentifierMigrationMarker($cacheMarkerFile);
    }

    public function migrateIfNeeded(): bool
    {
        if ($this->marker->isComplete()) {
            return false;
        }

        if (!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $this->marker->markComplete();

            return false;
        }

        if (!$this->requiresMigration()) {
            $this->marker->markComplete();

            return false;
        }

        $this->connection->beginTransaction();

        try {
            $this->prepareIdentifierMaps();
            $this->renameLegacyTables();
            $this->createReplacementTables();
            $this->copyLegacyRows();
            $this->reseedIdentityColumns();
            $this->dropLegacyTables();

            $this->connection->commit();
            $this->marker->markComplete();

            return true;
        } catch (\Throwable $throwable) {
            $this->connection->rollBack();
            throw $throwable;
        }
    }

    private function requiresMigration(): bool
    {
        $attachmentIdType = (string) $this->connection->fetchOne($this->sql->attachmentIdDataType());
        $attachmentLinkIdType = (string) $this->connection->fetchOne($this->sql->attachmentLinkIdDataType());

        return !\in_array($attachmentIdType, ['integer', 'bigint'], true)
            || !\in_array($attachmentLinkIdType, ['integer', 'bigint'], true);
    }

    private function prepareIdentifierMaps(): void
    {
        $this->connection->executeStatement($this->sql->createAttachmentIdMap());
        $this->connection->executeStatement($this->sql->populateAttachmentIdMap());
        $this->connection->executeStatement($this->sql->createAttachmentLinkIdMap());
        $this->connection->executeStatement($this->sql->populateAttachmentLinkIdMap());
    }

    private function renameLegacyTables(): void
    {
        $this->connection->executeStatement('ALTER TABLE attachment_link DROP CONSTRAINT IF EXISTS fk_attachment_link_attachment');
        $this->connection->executeStatement('ALTER TABLE attachment RENAME TO attachment_legacy');
        $this->connection->executeStatement('ALTER TABLE attachment_link RENAME TO attachment_link_legacy');
    }

    private function createReplacementTables(): void
    {
        $this->connection->executeStatement($this->sql->createAttachmentTable());
        $this->connection->executeStatement($this->sql->createAttachmentLinkTable());
    }

    private function copyLegacyRows(): void
    {
        $this->connection->executeStatement($this->sql->copyAttachments());
        $this->connection->executeStatement($this->sql->copyAttachmentLinks());
    }

    private function reseedIdentityColumns(): void
    {
        $this->connection->executeStatement($this->sql->reseedAttachmentIdentity());
        $this->connection->executeStatement($this->sql->reseedAttachmentLinkIdentity());
    }

    private function dropLegacyTables(): void
    {
        $this->connection->executeStatement('DROP TABLE attachment_link_legacy');
        $this->connection->executeStatement('DROP TABLE attachment_legacy');
    }
}
