<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\Value\ManageCrudResourceDefinition;

final class ManageCrudControllerGenerator
{
    public function __construct(
        private readonly string $bundleDir,
    ) {
    }

    /**
     * @param list<ManageCrudResourceDefinition> $resources
     *
     * @return list<class-string>
     */
    public function synchronize(array $resources): array
    {
        $generatedDir = $this->generatedDirectory();
        if (!is_dir($generatedDir)) {
            mkdir($generatedDir, 0777, true);
        }

        $generatedControllers = [];
        $resourcesByComponent = [];
        foreach ($resources as $resource) {
            if (!$resource->enabled || ManageCrudResourceDefinition::SURFACE_MANAGE !== $resource->surface) {
                continue;
            }

            $resourcesByComponent[$resource->componentKey][] = $resource;
        }

        foreach ($resourcesByComponent as $componentKey => $componentResources) {
            $primaryResource = $this->selectPrimaryResource($componentResources);
            if (null === $primaryResource) {
                continue;
            }

            $className = $this->controllerClassName($componentKey);
            $fqcn = $this->controllerFqcn($componentKey);
            $generatedControllers[] = $fqcn;

            $this->writeController(
                $generatedDir.'/'.$className.'.php',
                $fqcn,
                $primaryResource->entityClass,
                $componentKey,
            );
        }

        $this->removeStaleControllers($generatedDir, $generatedControllers);

        return $generatedControllers;
    }

    private function generatedDirectory(): string
    {
        return $this->bundleDir.'/src/Controller/Crud/Generated';
    }

    /**
     * @param list<ManageCrudResourceDefinition> $resources
     */
    private function selectPrimaryResource(array $resources): ?ManageCrudResourceDefinition
    {
        usort(
            $resources,
            function (ManageCrudResourceDefinition $left, ManageCrudResourceDefinition $right): int {
                $rightScore = $this->resourceScore($right);
                $leftScore = $this->resourceScore($left);

                if ($rightScore !== $leftScore) {
                    return $rightScore <=> $leftScore;
                }

                return [$left->resourceKey, $left->label] <=> [$right->resourceKey, $right->label];
            },
        );

        return $resources[0] ?? null;
    }

    private function resourceScore(ManageCrudResourceDefinition $resource): int
    {
        $subject = strtolower($resource->resourceKey.' '.$resource->label.' '.$resource->entityClass);
        $entityShortName = $this->shortClassName($resource->entityClass);
        $normalizedShortName = $this->normalizeShortName($entityShortName);
        $componentKey = $resource->componentKey;
        $preferredRoot = $this->preferredRootName($componentKey);
        $score = 0;

        if ('' !== $preferredRoot) {
            if ($normalizedShortName === $preferredRoot) {
                $score += 40;
            } elseif (str_starts_with($normalizedShortName, $preferredRoot)) {
                $score += 20;
            } elseif (str_contains($normalizedShortName, $preferredRoot)) {
                $score += 10;
            }
        }

        if ('tagging' === $componentKey) {
            if (str_ends_with($resource->entityClass, '\\TagAdminView')) {
                $score += 200;
            } elseif (str_ends_with($resource->entityClass, '\\TagOutboxEvent')) {
                $score -= 50;
            } elseif (str_ends_with($resource->entityClass, '\\Tag')) {
                $score -= 100;
            } elseif (str_ends_with($resource->entityClass, '\\TagPolicy')) {
                $score -= 50;
            }
        }

        if ('messaging' === $componentKey) {
            if (str_ends_with($resource->entityClass, '\\MessageAdminView')) {
                $score += 200;
            } elseif (str_ends_with($resource->entityClass, '\\MessageEntity')) {
                $score -= 100;
            }
        }

        foreach ([
            'attachment', 'audit', 'binding', 'change_request', 'control', 'delivery', 'destination',
            'event', 'history', 'idempotency', 'log', 'member', 'metric', 'outbox', 'pin', 'projection',
            'relation', 'review', 'snapshot', 'token', 'workflow', 'queue', 'batch', 'analytics',
            'credential', 'assignment', 'classification', 'reference', 'mapping', 'record', 'entity',
        ] as $technicalKeyword) {
            if (str_contains($subject, $technicalKeyword)) {
                $score -= 20;
            }
        }

        foreach ([
            'account', 'address', 'catalog', 'category', 'commission', 'content', 'currency', 'exchange',
            'message', 'order', 'page', 'payment', 'profile', 'product', 'record', 'shipment', 'tag',
            'tax', 'user', 'vendor',
        ] as $businessKeyword) {
            if (str_contains($subject, $businessKeyword)) {
                $score += 10;
            }
        }

        $score -= intdiv(strlen($normalizedShortName), 4);

        if (str_ends_with($entityShortName, 'Entity')) {
            $score -= 5;
        }

        return $score;
    }

    private function controllerClassName(string $componentKey): string
    {
        return $this->studly($componentKey).'CrudController';
    }

    private function controllerFqcn(string $componentKey): string
    {
        return 'App\\Managing\\Controller\\Crud\\Generated\\'.$this->controllerClassName($componentKey);
    }

    private function writeController(string $filePath, string $fqcn, string $entityClass, string $componentKey): void
    {
        $namespace = 'App\\Managing\\Controller\\Crud\\Generated';
        $className = basename(str_replace('\\', '/', $fqcn));
        $routeName = $componentKey;
        $routePath = '/'.$componentKey;
        $specialAttachmentMigration = 'attaching' === $componentKey;
        $readOnly = $this->isReadOnlyEntity($entityClass);
        $extraMethods = '';

        if ($specialAttachmentMigration) {
            $extraMethods .= <<<'PHP'

    public function index(\EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext $context): \EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore|\Symfony\Component\HttpFoundation\Response
    {
        $doctrine = $this->container->get('doctrine');
        $cacheDir = (string) $this->container->get('parameter_bag')->get('kernel.cache_dir');
        $connection = $doctrine->getConnection();
        $markerFile = $cacheDir.'/attaching_migration_complete.flag';
        (new \App\Managing\Service\Admin\AttachmentIdentifierMigrationService($connection, $markerFile))->migrateIfNeeded();

        return parent::index($context);
    }

PHP;
        }

        if ($readOnly) {
            $extraMethods = <<<'PHP'

    protected static function manageIsReadOnly(): bool
    {
        return true;
    }

PHP.$extraMethods;
        }

        $content = <<<PHP
<?php

declare(strict_types=1);

namespace {$namespace};

use App\\Managing\\Controller\\Crud\\AbstractManageContentCrudController;
use EasyCorp\\Bundle\\EasyAdminBundle\\Attribute\\AdminRoute;
use Symfony\\Component\\HttpKernel\\Attribute\\AsController;

#[AsController]
#[AdminRoute(path: '{$routePath}', name: '{$routeName}')]
final class {$className} extends AbstractManageContentCrudController
{
    public static function getEntityFqcn(): string
    {
        return \\{$entityClass}::class;
    }

{$extraMethods}}

PHP;

        if (is_file($filePath) && file_get_contents($filePath) === $content) {
            return;
        }

        file_put_contents($filePath, $content);
    }

    private function isReadOnlyEntity(string $entityClass): bool
    {
        $shortName = $this->shortClassName($entityClass);

        return str_ends_with($shortName, 'View') || str_contains($entityClass, '\\ReadModel\\') || str_ends_with($shortName, 'ReadModel');
    }

    /**
     * @param list<class-string> $generatedControllers
     */
    private function removeStaleControllers(string $generatedDir, array $generatedControllers): void
    {
        $desiredFiles = [];
        foreach ($generatedControllers as $fqcn) {
            $desiredFiles[$this->generatedDirectory().'/'.$this->shortClassName($fqcn).'.php'] = true;
        }

        foreach (glob($generatedDir.'/*CrudController.php') ?: [] as $file) {
            if (!isset($desiredFiles[$file])) {
                @unlink($file);
            }
        }
    }

    private function studly(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9]+/', ' ', $value) ?? $value;
        $value = ucwords(strtolower(trim($value)));

        return str_replace(' ', '', $value);
    }

    private function normalizeShortName(string $shortName): string
    {
        $shortName = preg_replace('/(?:Entity|Record|Projection|Model|Dto|View|ReadModel|WriteModel)$/', '', $shortName) ?? $shortName;

        return strtolower($shortName);
    }

    private function preferredRootName(string $componentKey): string
    {
        return match ($componentKey) {
            'attaching' => 'attachment',
            'billing' => 'billing',
            'cataloging' => 'catalog',
            'commissioning' => 'commission',
            'currencing' => 'currency',
            'exchanging' => 'exchange',
            'localizing' => 'localization',
            'messaging' => 'message',
            'ordering' => 'order',
            'paging' => 'page',
            'paying' => 'payment',
            'shipping' => 'shipment',
            'subscripting' => 'subscription',
            'tagging' => 'tag',
            'taxating' => 'tax',
            'vendoring' => 'vendor',
            default => strtolower($componentKey),
        };
    }

    private function shortClassName(string $fqcn): string
    {
        $position = strrpos($fqcn, '\\');

        return false === $position ? $fqcn : substr($fqcn, $position + 1);
    }
}
