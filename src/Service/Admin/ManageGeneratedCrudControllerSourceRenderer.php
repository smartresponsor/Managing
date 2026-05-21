<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

/**
 * Renders deterministic PHP source for generated EasyAdmin CRUD controllers.
 *
 * The writer owns filesystem synchronization, while this renderer owns only the
 * PHP source contract for generated controllers.
 */
final class ManageGeneratedCrudControllerSourceRenderer
{
    public function __construct(
        private readonly ManageCrudResourcePolicy $resourcePolicy = new ManageCrudResourcePolicy(),
    ) {
    }

    public function render(string $fqcn, string $entityClass, string $componentKey, string $customMethods = ''): string
    {
        $namespace = 'App\\Managing\\Controller\\Crud\\Generated';
        $className = $this->shortClassName($fqcn);
        $routeName = $componentKey;
        $routePath = '/'.$componentKey;
        $extraMethods = $this->extraMethods($entityClass, $componentKey);
        $traitUse = $this->traitUse($componentKey);

        return <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

use App\\Managing\\Controller\\Crud\\AbstractManageContentCrudController;
{$traitUse}use EasyCorp\\Bundle\\EasyAdminBundle\\Attribute\\AdminRoute;
use Symfony\\Component\\HttpKernel\\Attribute\\AsController;

#[AsController]
#[AdminRoute(path: '{$routePath}', name: '{$routeName}')]
final class {$className} extends AbstractManageContentCrudController
{
{$this->traitStatement($componentKey)}    public static function getEntityFqcn(): string
    {
        return \\{$entityClass}::class;
    }

{$extraMethods}{$customMethods}}

PHP;
    }

    private function traitUse(string $componentKey): string
    {
        if (!$this->resourcePolicy->requiresAttachmentIdentifierMigration($componentKey)) {
            return '';
        }

        return "use App\\Managing\\Controller\\Crud\\ManageAttachmentIdentifierMigrationTrait;\n";
    }

    private function traitStatement(string $componentKey): string
    {
        if (!$this->resourcePolicy->requiresAttachmentIdentifierMigration($componentKey)) {
            return '';
        }

        return "    use ManageAttachmentIdentifierMigrationTrait;\n\n";
    }

    private function extraMethods(string $entityClass, string $componentKey): string
    {
        $extraMethods = '';

        if ($this->isReadOnlyEntity($entityClass)) {
            $extraMethods .= <<<'PHP'
    protected static function manageIsReadOnly(): bool
    {
        return true;
    }

PHP;
        }

        if ($this->resourcePolicy->requiresAttachmentIdentifierMigration($componentKey)) {
            $extraMethods .= <<<'PHP'
    public function index(\EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext $context): \EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore|\Symfony\Component\HttpFoundation\Response
    {
        $this->migrateAttachmentIdentifierIfNeeded();

        return parent::index($context);
    }

PHP;
        }

        return $extraMethods;
    }

    private function isReadOnlyEntity(string $entityClass): bool
    {
        $shortName = $this->shortClassName($entityClass);

        return str_ends_with($shortName, 'View') || str_contains($entityClass, '\\ReadModel\\') || str_ends_with($shortName, 'ReadModel');
    }

    private function shortClassName(string $fqcn): string
    {
        $position = strrpos($fqcn, '\\');

        return false === $position ? $fqcn : substr($fqcn, $position + 1);
    }
}
