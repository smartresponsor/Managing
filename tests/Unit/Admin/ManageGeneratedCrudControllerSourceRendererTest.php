<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Service\Admin\ManageCrudResourcePolicy;
use App\Managing\Service\Admin\ManageGeneratedCrudControllerSourceRenderer;
use PHPUnit\Framework\TestCase;

final class ManageGeneratedCrudControllerSourceRendererTest extends TestCase
{
    public function testItRendersReadOnlyGeneratedCrudControllerSource(): void
    {
        $renderer = new ManageGeneratedCrudControllerSourceRenderer(new ManageCrudResourcePolicy());

        $source = $renderer->render(
            'App\\Managing\\Controller\\Crud\\Generated\\ReportingCrudController',
            'App\\Reporting\\ReadModel\\ReportReadModel',
            'reporting',
        );

        self::assertStringContainsString('final class ReportingCrudController', $source);
        self::assertStringContainsString("#[AdminRoute(path: '/reporting', name: 'reporting')]", $source);
        self::assertStringContainsString('protected static function manageIsReadOnly(): bool', $source);
        self::assertStringContainsString('return \\App\\Reporting\\ReadModel\\ReportReadModel::class;', $source);
    }

    public function testItRendersAttachmentMigrationBridgeForConfiguredComponent(): void
    {
        $renderer = new ManageGeneratedCrudControllerSourceRenderer(
            new ManageCrudResourcePolicy(componentsRequiringAttachmentIdentifierMigration: ['attaching']),
        );

        $source = $renderer->render(
            'App\\Managing\\Controller\\Crud\\Generated\\AttachingCrudController',
            'App\\Attaching\\Entity\\Attachment\\Attachment',
            'attaching',
        );

        self::assertStringContainsString('use App\\Managing\\Controller\\Crud\\ManageAttachmentIdentifierMigrationTrait;', $source);
        self::assertStringContainsString('use ManageAttachmentIdentifierMigrationTrait;', $source);
        self::assertStringContainsString('$this->migrateAttachmentIdentifierIfNeeded();', $source);
    }
}
