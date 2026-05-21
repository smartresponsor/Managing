<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Service\Admin\ManageCrudResourcePolicy;
use App\Managing\Service\Admin\ManageGeneratedCrudControllerWriter;
use PHPUnit\Framework\TestCase;

final class ManageGeneratedCrudControllerWriterTest extends TestCase
{
    public function testItRendersReadOnlyControllerWithoutMigrationTrait(): void
    {
        $writer = new ManageGeneratedCrudControllerWriter(sys_get_temp_dir(), new ManageCrudResourcePolicy());

        $source = $writer->controllerSource(
            'App\\Managing\\Controller\\Crud\\Generated\\ReportingCrudController',
            'App\\Reporting\\Entity\\ReportView',
            'reporting',
        );

        self::assertStringContainsString('final class ReportingCrudController', $source);
        self::assertStringContainsString('return \\App\\Reporting\\Entity\\ReportView::class;', $source);
        self::assertStringContainsString('protected static function manageIsReadOnly(): bool', $source);
        self::assertStringNotContainsString('ManageAttachmentIdentifierMigrationTrait', $source);
    }

    public function testItRendersAttachmentMigrationBridgeOnlyForConfiguredComponent(): void
    {
        $writer = new ManageGeneratedCrudControllerWriter(
            sys_get_temp_dir(),
            new ManageCrudResourcePolicy(componentsRequiringAttachmentIdentifierMigration: ['attaching']),
        );

        $source = $writer->controllerSource(
            'App\\Managing\\Controller\\Crud\\Generated\\AttachingCrudController',
            'App\\Attaching\\Entity\\Attachment\\Attachment',
            'attaching',
        );

        self::assertStringContainsString('use App\\Managing\\Controller\\Crud\\ManageAttachmentIdentifierMigrationTrait;', $source);
        self::assertStringContainsString('use ManageAttachmentIdentifierMigrationTrait;', $source);
        self::assertStringContainsString('$this->migrateAttachmentIdentifierIfNeeded();', $source);
        self::assertStringNotContainsString('new \\App\\Managing\\Service\\Admin\\AttachmentIdentifierMigrationService', $source);
    }

    public function testItPreservesGeneratedControllerCustomizationHooksDuringRewrite(): void
    {
        $writer = new ManageGeneratedCrudControllerWriter(sys_get_temp_dir(), new ManageCrudResourcePolicy());
        $existingSource = <<<'PHP'
<?php

final class ApplicatingCrudController
{
    public static function getEntityFqcn(): string
    {
        return OldEntity::class;
    }

    /** @return array<string, array<string, string>> */
    protected static function manageArrayChoiceFields(): array
    {
        return [
            'roles' => [
                'Application admin' => 'ROLE_APPLICATION_ADMIN',
            ],
        ];
    }
}
PHP;

        $source = $writer->controllerSource(
            'App\\Managing\\Controller\\Crud\\Generated\\ApplicatingCrudController',
            'App\\Applicating\\Entity\\ApplicationUser',
            'applicating',
            $existingSource,
        );

        self::assertStringContainsString('return \\App\\Applicating\\Entity\\ApplicationUser::class;', $source);
        self::assertStringContainsString('protected static function manageArrayChoiceFields(): array', $source);
        self::assertStringContainsString('ROLE_APPLICATION_ADMIN', $source);
    }
}
