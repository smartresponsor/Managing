<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Documentation;

use PHPUnit\Framework\TestCase;

final class ManageFieldVisibilityInspectionDocumentationTest extends TestCase
{
    public function testInspectionDocumentationMentionsCommandAndSafetyBoundary(): void
    {
        $path = dirname(__DIR__, 3).'/docs/manage-field-visibility-inspection.adoc';
        $contents = file_get_contents($path);

        self::assertIsString($contents);
        self::assertStringContainsString('managing:field-visibility:explain', $contents);
        self::assertStringContainsString('read-only', $contents);
        self::assertStringContainsString('cannot override deny decisions', $contents);
    }
}
