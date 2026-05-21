<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

/**
 * Temporary row-map SQL for the Attaching identifier migration.
 *
 * The map statements are separated from data-copy statements so the migration
 * contract keeps one narrow responsibility per SQL collaborator.
 */
final readonly class AttachmentIdentifierMigrationMapSql
{
    public function createAttachmentIdMap(): string
    {
        return 'CREATE TEMP TABLE attachment_id_map (old_id text PRIMARY KEY, new_id integer NOT NULL UNIQUE)';
    }

    public function populateAttachmentIdMap(): string
    {
        return <<<'SQL'
            INSERT INTO attachment_id_map (old_id, new_id)
            SELECT a.id::text, row_number() OVER (ORDER BY a.created_at, a.updated_at, a.id::text)::integer
            FROM attachment a
            SQL;
    }

    public function createAttachmentLinkIdMap(): string
    {
        return 'CREATE TEMP TABLE attachment_link_id_map (old_id text PRIMARY KEY, new_id integer NOT NULL UNIQUE)';
    }

    public function populateAttachmentLinkIdMap(): string
    {
        return <<<'SQL'
            INSERT INTO attachment_link_id_map (old_id, new_id)
            SELECT l.id::text, row_number() OVER (ORDER BY l.created_at, l.updated_at, l.id::text)::integer
            FROM attachment_link_legacy l
            SQL;
    }
}
