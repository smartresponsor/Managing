<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Documentation;

use PHPUnit\Framework\TestCase;

final class ManageFinalServiceLayerCleanupDocumentationTest extends TestCase
{
    public function testFinalCleanupDocumentCapturesServiceLayerCanon(): void
    {
        $document = file_get_contents(dirname(__DIR__, 3).'/docs/manage-final-service-layer-cleanup.adoc');

        self::assertIsString($document);
        self::assertStringContainsString('src/Service` must not contain PHP implementation classes', $document);
        self::assertStringContainsString('src/Migration/Admin/AttachmentIdentifier', $document);
        self::assertStringContainsString('Rolling field-value deny remains an access-axis deny', $document);
        self::assertStringContainsString('user view profiles remain presentation-only', $document);
    }
}
