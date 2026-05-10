<?php

declare(strict_types=1);

namespace App\Managing\Service\Workbench;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudActionUrlBuilderInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudResourceRegistryInterface;
use App\Managing\ServiceInterface\Form\ManageFormRegistryInterface;
use App\Managing\ServiceInterface\Probe\ManageProbeRegistryInterface;
use App\Managing\ServiceInterface\Relation\ManageRelationRegistryInterface;
use App\Managing\ServiceInterface\Route\ManageRouteRegistryInterface;
use App\Managing\ServiceInterface\Route\ManageRouteUrlResolverInterface;
use App\Managing\ServiceInterface\Workbench\ManageWorkbenchIndexBuilderInterface;

final readonly class ManageWorkbenchIndexBuilder implements ManageWorkbenchIndexBuilderInterface
{
    public function __construct(
        private ManageAdminRegistryInterface $adminRegistry,
        private ManageCrudResourceRegistryInterface $crudResourceRegistry,
        private ManageRouteRegistryInterface $routeRegistry,
        private ManageFormRegistryInterface $formRegistry,
        private ManageRelationRegistryInterface $relationRegistry,
        private ManageProbeRegistryInterface $probeRegistry,
        private ManageCrudActionUrlBuilderInterface $crudActionUrlBuilder,
        private ManageRouteUrlResolverInterface $routeUrlResolver,
    ) {
    }

    public function buildIndex(): array
    {
        $components = [];

        foreach ($this->adminRegistry->getComponents() as $component) {
            $components[$component->key] = [
                'component' => $component,
                'resources' => [],
                'routes' => [],
                'forms' => [],
                'relations' => [],
                'probes' => [],
                'readiness' => [
                    'resourcesWithoutActions' => 0,
                    'routesWithoutUrls' => 0,
                    'formsWithMissingTypes' => 0,
                    'relationsWithMissingResources' => 0,
                    'probes' => 0,
                ],
            ];
        }

        foreach ($this->crudResourceRegistry->getCrudResources() as $resource) {
            $component = $resource->componentKey;
            $this->ensureComponentBucket($components, $component);

            $actions = $this->crudActionUrlBuilder->buildActionUrls($resource);
            if ([] === $actions) {
                ++$components[$component]['readiness']['resourcesWithoutActions'];
            }

            $components[$component]['resources'][] = [
                'definition' => $resource,
                'actions' => $actions,
            ];
        }

        foreach ($this->routeRegistry->getRoutes() as $route) {
            $component = $route->componentKey;
            $this->ensureComponentBucket($components, $component);

            $url = $this->routeUrlResolver->resolveUrl($route);
            if (null === $url) {
                ++$components[$component]['readiness']['routesWithoutUrls'];
            }

            $components[$component]['routes'][] = [
                'definition' => $route,
                'url' => $url,
            ];
        }

        foreach ($this->formRegistry->getForms() as $form) {
            $component = $form->componentKey;
            $this->ensureComponentBucket($components, $component);

            if (!class_exists($form->formTypeClass)) {
                ++$components[$component]['readiness']['formsWithMissingTypes'];
            }

            $components[$component]['forms'][] = $form;
        }

        $resourcesByComponent = $this->indexResourcesByComponent($components);

        foreach ($this->relationRegistry->getRelations() as $relation) {
            $component = $relation->componentKey;
            $this->ensureComponentBucket($components, $component);

            $hasSource = isset($resourcesByComponent[$component][$relation->sourceResourceKey]);
            $hasTarget = isset($resourcesByComponent[$component][$relation->targetResourceKey]);

            if (!$hasSource || !$hasTarget) {
                ++$components[$component]['readiness']['relationsWithMissingResources'];
            }

            $components[$component]['relations'][] = [
                'definition' => $relation,
                'hasSource' => $hasSource,
                'hasTarget' => $hasTarget,
            ];
        }

        foreach ($this->probeRegistry->getProbes() as $probe) {
            $component = $probe->componentKey;
            $this->ensureComponentBucket($components, $component);

            ++$components[$component]['readiness']['probes'];
            $components[$component]['probes'][] = $probe;
        }

        return [
            'components' => array_values($components),
            'totals' => [
                'components' => count($components),
                'resources' => count($this->crudResourceRegistry->getCrudResources()),
                'routes' => count($this->routeRegistry->getRoutes()),
                'forms' => count($this->formRegistry->getForms()),
                'relations' => count($this->relationRegistry->getRelations()),
                'probes' => count($this->probeRegistry->getProbes()),
            ],
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $components
     */
    private function ensureComponentBucket(array &$components, string $componentKey): void
    {
        if (isset($components[$componentKey])) {
            return;
        }

        $components[$componentKey] = [
            'component' => null,
            'resources' => [],
            'routes' => [],
            'forms' => [],
            'relations' => [],
            'probes' => [],
            'readiness' => [
                'resourcesWithoutActions' => 0,
                'routesWithoutUrls' => 0,
                'formsWithMissingTypes' => 0,
                'relationsWithMissingResources' => 0,
                'probes' => 0,
            ],
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $components
     *
     * @return array<string, array<string, true>>
     */
    private function indexResourcesByComponent(array $components): array
    {
        $index = [];

        foreach ($components as $componentKey => $component) {
            foreach ($component['resources'] as $resourceRow) {
                $resource = $resourceRow['definition'];
                $index[$componentKey][$resource->resourceKey] = true;
            }
        }

        return $index;
    }
}
