<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Documentation;

use PHPUnit\Framework\TestCase;

final class ManageFieldExternalRollingAccessDocumentationTest extends TestCase
{
    public function testDocumentationMentionsOptionalRollingBackendAndFailClosedMode(): void
    {
        $path = dirname(__DIR__, 3).'/docs/manage-field-external-rolling-access.adoc';
        $contents = file_get_contents($path);

        self::assertIsString($contents);
        self::assertStringContainsString('crud_field_external_access_backend: rolling', $contents);
        self::assertStringContainsString('crud_field_external_access_failure_effect: deny', $contents);
        self::assertStringContainsString('A Rolling deny is terminal', $contents);
    }
}
