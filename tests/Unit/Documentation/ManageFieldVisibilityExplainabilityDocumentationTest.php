<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Documentation;

use PHPUnit\Framework\TestCase;

final class ManageFieldVisibilityExplainabilityDocumentationTest extends TestCase
{
    public function testExplainabilityDocumentationDistinguishesAccessAndPresentationAxes(): void
    {
        $path = dirname(__DIR__, 3).'/docs/manage-field-visibility-explainability.adoc';
        $contents = file_get_contents($path);

        self::assertIsString($contents);
        self::assertStringContainsString('Decision axes', $contents);
        self::assertStringContainsString('rolling_field_value_access_denied', $contents);
        self::assertStringContainsString('User presentation hide', $contents);
        self::assertStringContainsString('decisionAxis', $contents);
    }
}
