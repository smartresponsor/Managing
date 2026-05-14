<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageComponentDetailBuilderInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudActionUrlBuilderInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudResourceRegistryInterface;
use App\Managing\ServiceInterface\Form\ManageFormRegistryInterface;
use App\Managing\ServiceInterface\Probe\ManageProbeRegistryInterface;
use App\Managing\ServiceInterface\Relation\ManageRelationRegistryInterface;
use App\Managing\ServiceInterface\Route\ManageRouteRegistryInterface;
use App\Managing\Value\ManageComponentDefinition;

final readonly class ManageComponentDetailBuilder implements ManageComponentDetailBuilderInterface
{
    public function __construct(
        private ManageAdminRegistryInterface $adminRegistry,
        private ManageCrudResourceRegistryInterface $crudResourceRegistry,
        private ManageCrudActionUrlBuilderInterface $actionUrlBuilder,
        private ManageRouteRegistryInterface $routeRegistry,
        private ManageFormRegistryInterface $formRegistry,
        private ManageRelationRegistryInterface $relationRegistry,
        private ManageProbeRegistryInterface $probeRegistry,
    ) {
    }

    public function buildComponentDetail(string $componentKey): ?array
    {
        $component = $this->findComponent($componentKey);

        if (!$component instanceof ManageComponentDefinition) {
            return null;
        }

        return [
            'component' => $component,
            'resourceRows' => $this->buildResourceRows($componentKey),
            'routes' => $this->filterByComponent($this->routeRegistry->getRoutes(), $componentKey),
            'forms' => $this->filterByComponent($this->formRegistry->getForms(), $componentKey),
            'relations' => $this->filterByComponent($this->relationRegistry->getRelations(), $componentKey),
            'probes' => $this->filterByComponent($this->probeRegistry->getProbes(), $componentKey),
        ];
    }

    private function findComponent(string $componentKey): ?ManageComponentDefinition
    {
        foreach ($this->adminRegistry->getComponents() as $component) {
            if ($component->key === $componentKey) {
                return $component;
            }
        }

        return null;
    }

    /**
     * @param iterable<object> $items
     *
     * @return list<object>
     */
    private function filterByComponent(iterable $items, string $componentKey): array
    {
        $filtered = [];

        foreach ($items as $item) {
            if (property_exists($item, 'componentKey') && $item->componentKey === $componentKey) {
                $filtered[] = $item;
            }
        }

        return $filtered;
    }

    /**
     * @return list<array{resource: object, actions: array<string, string>}>
     */
    private function buildResourceRows(string $componentKey): array
    {
        $rows = [];

        foreach ($this->crudResourceRegistry->getCrudResources() as $resource) {
            if (!property_exists($resource, 'componentKey') || $resource->componentKey !== $componentKey) {
                continue;
            }

            $rows[] = [
                'resource' => $resource,
                'actions' => $this->actionUrlBuilder->buildActionUrls($resource),
            ];
        }

        return $rows;
    }
}
