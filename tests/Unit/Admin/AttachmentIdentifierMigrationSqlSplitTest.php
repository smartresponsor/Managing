<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Service\Admin\AttachmentIdentifierMigrationCopySql;
use App\Managing\Service\Admin\AttachmentIdentifierMigrationDataSql;
use App\Managing\Service\Admin\AttachmentIdentifierMigrationMapSql;
use App\Managing\Service\Admin\AttachmentIdentifierMigrationMetadataSql;
use App\Managing\Service\Admin\AttachmentIdentifierMigrationSchemaSql;
use App\Managing\Service\Admin\AttachmentIdentifierMigrationSql;
use PHPUnit\Framework\TestCase;

final class AttachmentIdentifierMigrationSqlSplitTest extends TestCase
{
    public function testFacadeDelegatesDataCopyStatementsToDataSql(): void
    {
        $facade = new AttachmentIdentifierMigrationSql(dataSql: new AttachmentIdentifierMigrationDataSql());

        self::assertStringContainsString('CREATE TEMP TABLE attachment_id_map', $facade->createAttachmentIdMap());
        self::assertStringContainsString('FROM attachment_legacy', $facade->copyAttachments());
        self::assertStringContainsString('FROM attachment_link_legacy', $facade->copyAttachmentLinks());
    }

    public function testDataSqlDelegatesMapAndCopyStatements(): void
    {
        $dataSql = new AttachmentIdentifierMigrationDataSql(
            mapSql: new AttachmentIdentifierMigrationMapSql(),
            copySql: new AttachmentIdentifierMigrationCopySql(),
        );

        self::assertStringContainsString('attachment_id_map', $dataSql->createAttachmentIdMap());
        self::assertStringContainsString('attachment_link_id_map', $dataSql->createAttachmentLinkIdMap());
        self::assertStringContainsString('FROM attachment_legacy', $dataSql->copyAttachments());
        self::assertStringContainsString('FROM attachment_link_legacy', $dataSql->copyAttachmentLinks());
    }

    public function testFacadeDelegatesSchemaStatementsToSchemaSql(): void
    {
        $facade = new AttachmentIdentifierMigrationSql(schemaSql: new AttachmentIdentifierMigrationSchemaSql());

        self::assertStringContainsString('CREATE TABLE attachment', $facade->createAttachmentTable());
        self::assertStringContainsString('FOREIGN KEY (attachment_id)', $facade->createAttachmentLinkTable());
        self::assertStringContainsString('pg_get_serial_sequence', $facade->reseedAttachmentIdentity());
    }

    public function testFacadeDelegatesMetadataStatementsToMetadataSql(): void
    {
        $facade = new AttachmentIdentifierMigrationSql(metadataSql: new AttachmentIdentifierMigrationMetadataSql());

        self::assertStringContainsString('information_schema.columns', $facade->attachmentIdDataType());
        self::assertStringContainsString('attachment_link', $facade->attachmentLinkIdDataType());
    }
}
