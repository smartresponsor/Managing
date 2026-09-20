<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Trait\Crud\ManageAttachmentIdentifierMigrationTrait;
use PHPUnit\Framework\TestCase;

final class AttachmentIdentifierMigrationSupportTest extends TestCase
{
    public function testManagingNoLongerShipsAttachmentMigrationServices(): void
    {
        foreach ([
            'AttachmentIdentifierMigrationMarker',
            'AttachmentIdentifierMigrationSql',
            'AttachmentIdentifierMigrationDataSql',
            'AttachmentIdentifierMigrationSchemaSql',
            'AttachmentIdentifierMigrationMetadataSql',
        ] as $className) {
            self::assertFalse(class_exists('App\\Managing\\Migration\\Admin\\AttachmentIdentifier\\'.$className));
        }
    }

    public function testCompatibilityHookIsIntentionallyNoOp(): void
    {
        $bridge = new class {
            use ManageAttachmentIdentifierMigrationTrait;

            public function run(): void
            {
                $this->migrateAttachmentIdentifierIfNeeded();
            }
        };

        $bridge->run();
        self::assertTrue(true);
    }
}
