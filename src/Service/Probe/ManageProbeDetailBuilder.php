<?php

declare(strict_types=1);

namespace App\Managing\Service\Probe;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudResourceRegistryInterface;
use App\Managing\ServiceInterface\Form\ManageFormRegistryInterface;
use App\Managing\ServiceInterface\Probe\ManageProbeDetailBuilderInterface;
use App\Managing\ServiceInterface\Probe\ManageProbeRegistryInterface;
use App\Managing\ServiceInterface\Relation\ManageRelationRegistryInterface;
use App\Managing\ServiceInterface\Route\ManageRouteRegistryInterface;
use App\Managing\Value\ManageComponentDefinition;
use App\Managing\Value\ManageProbeDefinition;

final readonly class ManageProbeDetailBuilder implements ManageProbeDetailBuilderInterface
{
    public function __construct(
        private ManageProbeRegistryInterface $probeRegistry,
        private ManageAdminRegistryInterface $adminRegistry,
        private ManageCrudResourceRegistryInterface $crudResourceRegistry,
        private ManageRouteRegistryInterface $routeRegistry,
        private ManageFormRegistryInterface $formRegistry,
        private ManageRelationRegistryInterface $relationRegistry,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buildProbeDetail(string $componentKey, string $probeKey): ?array
    {
        $probe = $this->findProbe($componentKey, $probeKey);

        if (!$probe instanceof ManageProbeDefinition) {
            return null;
        }

        return [
            'probe' => $probe,
            'component' => $this->findComponent($componentKey),
            'relatedResources' => $this->findByComponent($componentKey, $this->crudResourceRegistry->getCrudResources()),
            'relatedRoutes' => $this->findByComponent($componentKey, $this->routeRegistry->getRoutes()),
            'relatedForms' => $this->findByComponent($componentKey, $this->formRegistry->getForms()),
            'relatedRelations' => $this->findByComponent($componentKey, $this->relationRegistry->getRelations()),
            'nativeRunner' => $this->hasNativeRunner($probe),
        ];
    }

    private function findProbe(string $componentKey, string $probeKey): ?ManageProbeDefinition
    {
        foreach ($this->probeRegistry->getProbes() as $probe) {
            if ($probe->componentKey === $componentKey && $probe->probeKey === $probeKey) {
                return $probe;
            }
        }

        return null;
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
     * @param iterable<object> $definitions
     *
     * @return list<object>
     */
    private function findByComponent(string $componentKey, iterable $definitions): array
    {
        $matches = [];

        foreach ($definitions as $definition) {
            if (property_exists($definition, 'componentKey') && $definition->componentKey === $componentKey) {
                $matches[] = $definition;
            }
        }

        return $matches;
    }

    private function hasNativeRunner(ManageProbeDefinition $probe): bool
    {
        return 'managing' === $probe->componentKey && 'manage_canon_guard' === $probe->probeKey;
    }
}
