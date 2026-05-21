<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Service\Admin\AttachmentIdentifierMigrationMarker;
use App\Managing\Service\Admin\AttachmentIdentifierMigrationSql;
use PHPUnit\Framework\TestCase;

final class AttachmentIdentifierMigrationSupportTest extends TestCase
{
    public function testMarkerCreatesParentDirectoryAndMarkerFile(): void
    {
        $markerFile = sys_get_temp_dir().'/managing-attachment-migration-'.bin2hex(random_bytes(6)).'/marker.done';
        $marker = new AttachmentIdentifierMigrationMarker($markerFile);

        self::assertFalse($marker->isComplete());

        $marker->markComplete();

        self::assertTrue($marker->isComplete());
        self::assertSame('1', file_get_contents($markerFile));
    }

    public function testSqlContractKeepsSchemaAndCopyStatementsOutsideOrchestrator(): void
    {
        $sql = new AttachmentIdentifierMigrationSql();

        self::assertStringContainsString('CREATE TABLE attachment', $sql->createAttachmentTable());
        self::assertStringContainsString('FOREIGN KEY (attachment_id)', $sql->createAttachmentLinkTable());
        self::assertStringContainsString('FROM attachment_legacy', $sql->copyAttachments());
        self::assertStringContainsString('FROM attachment_link_legacy', $sql->copyAttachmentLinks());
        self::assertStringContainsString('information_schema.columns', $sql->attachmentIdDataType());
    }
}
