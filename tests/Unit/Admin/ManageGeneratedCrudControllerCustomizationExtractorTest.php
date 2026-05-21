<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Service\Admin\ManageGeneratedCrudControllerCustomizationExtractor;
use PHPUnit\Framework\TestCase;

final class ManageGeneratedCrudControllerCustomizationExtractorTest extends TestCase
{
    public function testItExtractsProtectedManageHooksWithDocblocks(): void
    {
        $source = <<<'PHP'
<?php

final class ExampleCrudController
{
    public static function getEntityFqcn(): string
    {
        return Example::class;
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

    public function index(): void
    {
    }
}
PHP;

        $block = (new ManageGeneratedCrudControllerCustomizationExtractor())->extractCustomMethodsBlock($source);

        self::assertStringContainsString('protected static function manageArrayChoiceFields(): array', $block);
        self::assertStringContainsString('ROLE_APPLICATION_ADMIN', $block);
        self::assertStringNotContainsString('public function index', $block);
        self::assertStringNotContainsString('getEntityFqcn', $block);
    }
}
