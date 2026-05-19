<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

final readonly class AttachmentIdentifierMigrationService
{
    public function __construct(
        private Connection $connection,
        private ?string $cacheMarkerFile = null,
    ) {
    }

    public function migrateIfNeeded(): bool
    {
        if ($this->isMigrationMarkedComplete()) {
            return false;
        }

        if (!$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $this->markMigrationComplete();

            return false;
        }

        if (!$this->requiresMigration()) {
            $this->markMigrationComplete();

            return false;
        }

        $this->connection->beginTransaction();

        try {
            $this->connection->executeStatement('CREATE TEMP TABLE attachment_id_map (old_id text PRIMARY KEY, new_id integer NOT NULL UNIQUE)');
            $this->connection->executeStatement(
                <<<'SQL'
                INSERT INTO attachment_id_map (old_id, new_id)
                SELECT a.id::text, row_number() OVER (ORDER BY a.created_at, a.updated_at, a.id::text)::integer
                FROM attachment a
                SQL
            );

            $this->connection->executeStatement('ALTER TABLE attachment_link DROP CONSTRAINT IF EXISTS fk_attachment_link_attachment');
            $this->connection->executeStatement('ALTER TABLE attachment RENAME TO attachment_legacy');
            $this->connection->executeStatement('ALTER TABLE attachment_link RENAME TO attachment_link_legacy');

            $this->createAttachmentTable();
            $this->createAttachmentLinkTable();

            $this->connection->executeStatement(
                <<<'SQL'
                INSERT INTO attachment (
                    id,
                    type,
                    media_kind,
                    document_kind,
                    storage_kind,
                    visibility,
                    status,
                    original_name,
                    stored_name,
                    extension,
                    mime_type,
                    size,
                    checksum,
                    storage_path,
                    title,
                    description,
                    alt_text,
                    width,
                    height,
                    duration_ms,
                    page_count,
                    created_at,
                    updated_at,
                    deleted_at
                )
                SELECT
                    m.new_id,
                    a.type,
                    a.media_kind,
                    a.document_kind,
                    a.storage_kind,
                    a.visibility,
                    a.status,
                    a.original_name,
                    a.stored_name,
                    a.extension,
                    a.mime_type,
                    a.size,
                    a.checksum,
                    a.storage_path,
                    a.title,
                    a.description,
                    a.alt_text,
                    a.width,
                    a.height,
                    a.duration_ms,
                    a.page_count,
                    a.created_at,
                    a.updated_at,
                    a.deleted_at
                FROM attachment_legacy a
                INNER JOIN attachment_id_map m ON m.old_id = a.id::text
                ORDER BY m.new_id
                SQL
            );

            $this->connection->executeStatement('CREATE TEMP TABLE attachment_link_id_map (old_id text PRIMARY KEY, new_id integer NOT NULL UNIQUE)');
            $this->connection->executeStatement(
                <<<'SQL'
                INSERT INTO attachment_link_id_map (old_id, new_id)
                SELECT l.id::text, row_number() OVER (ORDER BY l.created_at, l.updated_at, l.id::text)::integer
                FROM attachment_link_legacy l
                SQL
            );

            $this->connection->executeStatement(
                <<<'SQL'
                INSERT INTO attachment_link (
                    id,
                    attachment_id,
                    owner_type,
                    owner_id,
                    context,
                    slot,
                    position,
                    is_primary,
                    created_at,
                    updated_at
                )
                SELECT
                    lm.new_id,
                    am.new_id,
                    l.owner_type,
                    l.owner_id,
                    l.context,
                    l.slot,
                    l.position,
                    l.is_primary,
                    l.created_at,
                    l.updated_at
                FROM attachment_link_legacy l
                INNER JOIN attachment_id_map am ON am.old_id = l.attachment_id::text
                INNER JOIN attachment_link_id_map lm ON lm.old_id = l.id::text
                ORDER BY lm.new_id
                SQL
            );

            $this->reseedIdentityColumns();

            $this->connection->executeStatement('DROP TABLE attachment_link_legacy');
            $this->connection->executeStatement('DROP TABLE attachment_legacy');
            $this->connection->commit();
            $this->markMigrationComplete();

            return true;
        } catch (\Throwable $throwable) {
            $this->connection->rollBack();
            throw $throwable;
        }
    }

    private function requiresMigration(): bool
    {
        $attachmentIdType = (string) $this->connection->fetchOne(
            <<<'SQL'
            SELECT data_type
            FROM information_schema.columns
            WHERE table_name = 'attachment' AND column_name = 'id'
            LIMIT 1
            SQL
        );

        $attachmentLinkIdType = (string) $this->connection->fetchOne(
            <<<'SQL'
            SELECT data_type
            FROM information_schema.columns
            WHERE table_name = 'attachment_link' AND column_name = 'id'
            LIMIT 1
            SQL
        );

        return !\in_array($attachmentIdType, ['integer', 'bigint'], true)
            || !\in_array($attachmentLinkIdType, ['integer', 'bigint'], true);
    }

    private function createAttachmentTable(): void
    {
        $this->connection->executeStatement(
            <<<'SQL'
            CREATE TABLE attachment (
                id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
                type varchar(255) NOT NULL,
                media_kind varchar(255) DEFAULT NULL,
                document_kind varchar(255) DEFAULT NULL,
                storage_kind varchar(255) NOT NULL,
                visibility varchar(255) NOT NULL,
                status varchar(255) NOT NULL,
                original_name varchar(255) NOT NULL,
                stored_name varchar(255) NOT NULL,
                extension varchar(32) DEFAULT NULL,
                mime_type varchar(191) NOT NULL,
                size integer NOT NULL,
                checksum varchar(128) NOT NULL,
                storage_path varchar(1024) NOT NULL,
                title varchar(255) DEFAULT NULL,
                description text DEFAULT NULL,
                alt_text text DEFAULT NULL,
                width integer DEFAULT NULL,
                height integer DEFAULT NULL,
                duration_ms integer DEFAULT NULL,
                page_count integer DEFAULT NULL,
                created_at timestamp(0) without time zone NOT NULL,
                updated_at timestamp(0) without time zone NOT NULL,
                deleted_at timestamp(0) without time zone DEFAULT NULL
            )
            SQL
        );
    }

    private function createAttachmentLinkTable(): void
    {
        $this->connection->executeStatement(
            <<<'SQL'
            CREATE TABLE attachment_link (
                id integer GENERATED BY DEFAULT AS IDENTITY PRIMARY KEY,
                attachment_id integer NOT NULL,
                owner_type varchar(191) NOT NULL,
                owner_id varchar(191) NOT NULL,
                context varchar(191) DEFAULT NULL,
                slot varchar(191) DEFAULT NULL,
                position integer NOT NULL,
                is_primary boolean NOT NULL,
                created_at timestamp(0) without time zone NOT NULL,
                updated_at timestamp(0) without time zone NOT NULL,
                CONSTRAINT fk_attachment_link_attachment FOREIGN KEY (attachment_id) REFERENCES attachment (id) ON DELETE CASCADE
            )
            SQL
        );
    }

    private function reseedIdentityColumns(): void
    {
        $this->connection->executeStatement(
            <<<'SQL'
            SELECT setval(pg_get_serial_sequence('attachment', 'id'), (SELECT COALESCE(MAX(id), 0) FROM attachment), true)
            SQL
        );
        $this->connection->executeStatement(
            <<<'SQL'
            SELECT setval(pg_get_serial_sequence('attachment_link', 'id'), (SELECT COALESCE(MAX(id), 0) FROM attachment_link), true)
            SQL
        );
    }

    private function isMigrationMarkedComplete(): bool
    {
        return null !== $this->cacheMarkerFile && is_file($this->cacheMarkerFile);
    }

    private function markMigrationComplete(): void
    {
        if (null === $this->cacheMarkerFile) {
            return;
        }

        $directory = dirname($this->cacheMarkerFile);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($this->cacheMarkerFile, '1', LOCK_EX);
    }
}
