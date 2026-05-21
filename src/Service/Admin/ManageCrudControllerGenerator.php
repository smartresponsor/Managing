<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\Service\Admin\Host\ManageClassNameFormatter;
use App\Managing\Value\ManageCrudResourceDefinition;

final class ManageCrudControllerGenerator
{
    private ManageGeneratedCrudControllerWriter $writer;
    private ManageClassNameFormatter $nameFormatter;

    public function __construct(
        private readonly string $bundleDir,
        private readonly ManageCrudResourcePolicy $resourcePolicy = new ManageCrudResourcePolicy(),
        ?ManageGeneratedCrudControllerWriter $writer = null,
        ?ManageClassNameFormatter $nameFormatter = null,
    ) {
        $this->writer = $writer ?? new ManageGeneratedCrudControllerWriter($this->bundleDir, $this->resourcePolicy);
        $this->nameFormatter = $nameFormatter ?? new ManageClassNameFormatter();
    }

    /**
     * @param list<ManageCrudResourceDefinition> $resources
     *
     * @return list<class-string>
     */
    public function synchronize(array $resources): array
    {
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

            $fqcn = $this->controllerFqcn($componentKey);
            $generatedControllers[] = $fqcn;

            $this->writer->writeController(
                $fqcn,
                $primaryResource->entityClass,
                $componentKey,
            );
        }

        $this->writer->removeStaleControllers($generatedControllers);

        return $generatedControllers;
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
        $entityShortName = $this->nameFormatter->shortClassName($resource->entityClass);
        $normalizedShortName = $this->nameFormatter->normalizeResourceShortName($entityShortName);

        return $this->resourcePolicy->scoreResource($resource, $entityShortName, $normalizedShortName);
    }

    private function controllerFqcn(string $componentKey): string
    {
        return 'App\\Managing\\Controller\\Crud\\Generated\\'.$this->nameFormatter->studly($componentKey).'CrudController';
    }
}
