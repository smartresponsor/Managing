<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Documentation;

use PHPUnit\Framework\TestCase;

final class ManageFieldAccessVisibilityReadinessDocumentationTest extends TestCase
{
    public function testReadinessDocumentFixesCrossComponentBoundaries(): void
    {
        $doc = file_get_contents(dirname(__DIR__, 3).'/docs/manage-field-access-visibility-readiness.adoc');

        self::assertIsString($doc);
        self::assertStringContainsString('Managing owns the runtime EasyAdmin execution boundary', $doc);
        self::assertStringContainsString('Rolling-backed field-value access deny must stop field emission', $doc);
        self::assertStringContainsString('User personal profiles can change presentation only', $doc);
        self::assertStringContainsString('doctrine.orm.system_entity_manager', $doc);
        self::assertStringContainsString('managing:field-view-profile-storage:check', $doc);
        self::assertStringContainsString('rolling_field_value_access_denied', $doc);
        self::assertStringContainsString('access', $doc);
        self::assertStringContainsString('presentation', $doc);
        self::assertStringContainsString('availability', $doc);
    }
}
