<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Policy\Admin\ManageCrudResourcePolicy;
use App\Managing\Renderer\Admin\ManageGeneratedCrudControllerSourceRenderer;
use PHPUnit\Framework\TestCase;

final class AttachmentIdentifierMigrationSqlSplitTest extends TestCase
{
    public function testGeneratedControllerDoesNotOwnAttachmentMigrationByDefault(): void
    {
        $source = (new ManageGeneratedCrudControllerSourceRenderer())->render(
            'App\\Managing\\Controller\\Crud\\Generated\\AttachingCrudController',
            'App\\Attaching\\Entity\\Persistence\\Attachment\\Attachment',
            'attaching',
        );

        self::assertStringNotContainsString('ManageAttachmentIdentifierMigrationTrait', $source);
        self::assertStringNotContainsString('migrateAttachmentIdentifierIfNeeded', $source);
        self::assertStringNotContainsString('CREATE TABLE', $source);
    }

    public function testExplicitCompatibilityPolicyEmitsOnlyTheNoOpBridge(): void
    {
        $renderer = new ManageGeneratedCrudControllerSourceRenderer(new ManageCrudResourcePolicy(
            componentsRequiringAttachmentIdentifierMigration: ['attaching'],
        ));
        $source = $renderer->render(
            'App\\Managing\\Controller\\Crud\\Generated\\AttachingCrudController',
            'App\\Attaching\\Entity\\Persistence\\Attachment\\Attachment',
            'attaching',
        );

        self::assertStringContainsString('ManageAttachmentIdentifierMigrationTrait', $source);
        self::assertStringContainsString('migrateAttachmentIdentifierIfNeeded', $source);
        self::assertStringNotContainsString('attachment_legacy', $source);
        self::assertStringNotContainsString('information_schema', $source);
    }
}
