<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Documentation;

use PHPUnit\Framework\TestCase;

final class ManageFieldViewProfileHostActivationDocumentationTest extends TestCase
{
    public function testHostActivationDocumentKeepsProfileStorageInSystemEntityManager(): void
    {
        $root = dirname(__DIR__, 3);
        $contents = file_get_contents($root.'/docs/manage-field-view-profile-host-activation.adoc');

        self::assertIsString($contents);
        self::assertStringContainsString('doctrine.orm.system_entity_manager', $contents);
        self::assertStringContainsString('manage_crud_field_view_profile_rule', $contents);
        self::assertStringContainsString('system/internal EntityManager', $contents);
        self::assertStringContainsString('Do not map this entity to the user/business PostgreSQL EntityManager.', $contents);
    }

    public function testExampleConfigDoesNotEnableDefaultEntityManagerStorage(): void
    {
        $root = dirname(__DIR__, 3);
        $contents = file_get_contents($root.'/docs/examples/manage-field-view-profile-system-doctrine.yaml.example');

        self::assertIsString($contents);
        self::assertStringContainsString('entity_managers:', $contents);
        self::assertStringContainsString('system:', $contents);
        self::assertStringContainsString("crud_field_user_profile_entity_manager_service: 'doctrine.orm.system_entity_manager'", $contents);
        self::assertStringNotContainsString('doctrine.orm.default_entity_manager', $contents);
    }
}
