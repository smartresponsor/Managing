<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

/**
 * Metadata SQL used to detect whether the temporary Attaching identifier
 * migration is still required for a host database.
 */
final readonly class AttachmentIdentifierMigrationMetadataSql
{
    public function attachmentIdDataType(): string
    {
        return <<<'SQL'
            SELECT data_type
            FROM information_schema.columns
            WHERE table_name = 'attachment' AND column_name = 'id'
            LIMIT 1
            SQL;
    }

    public function attachmentLinkIdDataType(): string
    {
        return <<<'SQL'
            SELECT data_type
            FROM information_schema.columns
            WHERE table_name = 'attachment_link' AND column_name = 'id'
            LIMIT 1
            SQL;
    }
}
