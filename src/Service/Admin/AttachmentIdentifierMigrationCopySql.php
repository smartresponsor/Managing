<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

/**
 * Data-copy SQL for the Attaching identifier migration.
 *
 * Copy statements are kept separate from temporary map creation and schema
 * statements so the migration flow can be reviewed in smaller contracts.
 */
final readonly class AttachmentIdentifierMigrationCopySql
{
    public function copyAttachments(): string
    {
        return <<<'SQL'
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
            SQL;
    }

    public function copyAttachmentLinks(): string
    {
        return <<<'SQL'
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
            SQL;
    }
}
