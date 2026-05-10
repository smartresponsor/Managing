<?php

declare(strict_types=1);

namespace App\Managing\Service\Diagnostics;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudActionUrlBuilderInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudingRouteBridgeInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudResourceRegistryInterface;
use App\Managing\ServiceInterface\Diagnostics\ManageAdminDiagnosticsBuilderInterface;
use App\Managing\ServiceInterface\Form\ManageFormRegistryInterface;
use App\Managing\ServiceInterface\Probe\ManageProbeRegistryInterface;
use App\Managing\ServiceInterface\Relation\ManageRelationRegistryInterface;
use App\Managing\ServiceInterface\Route\ManageRouteRegistryInterface;
use App\Managing\Value\ManageCrudResourceDefinition;

final readonly class ManageAdminDiagnosticsBuilder implements ManageAdminDiagnosticsBuilderInterface
{
    public function __construct(
        private ManageAdminRegistryInterface $adminRegistry,
        private ManageCrudResourceRegistryInterface $crudResourceRegistry,
        private ManageRouteRegistryInterface $routeRegistry,
        private ManageFormRegistryInterface $formRegistry,
        private ManageRelationRegistryInterface $relationRegistry,
        private ManageProbeRegistryInterface $probeRegistry,
        private ManageCrudActionUrlBuilderInterface $actionUrlBuilder,
        private ManageCrudingRouteBridgeInterface $crudingRouteBridge,
    ) {
    }

    public function buildDiagnostics(): array
    {
        $resources = $this->crudResourceRegistry->getCrudResources();
        $resourceRows = [];
        $modeCounts = [
            ManageCrudResourceDefinition::MODE_EASYADMIN => 0,
            ManageCrudResourceDefinition::MODE_CRUDING_LINK => 0,
            ManageCrudResourceDefinition::MODE_CUSTOM_ROUTE => 0,
            'other' => 0,
        ];
        $actionable = 0;
        $missingActions = 0;

        foreach ($resources as $resource) {
            $modeCounts[$resource->mode] = ($modeCounts[$resource->mode] ?? 0) + 1;
            if (!isset($modeCounts[$resource->mode])) {
                ++$modeCounts['other'];
            }

            $actions = $this->actionUrlBuilder->buildActionUrls($resource);
            if ([] === $actions) {
                ++$missingActions;
            } else {
                ++$actionable;
            }

            $resourceRows[] = [
                'component' => $resource->componentKey,
                'resource' => $resource->resourceKey,
                'mode' => $resource->mode,
                'enabled' => $resource->enabled,
                'resourcePath' => $resource->resourcePath,
                'actions' => array_keys($actions),
            ];
        }

        return [
            'counts' => [
                'providers' => count($this->adminRegistry->getProviders()),
                'components' => count($this->adminRegistry->getComponents()),
                'crudResources' => count($resources),
                'routes' => count($this->routeRegistry->getRoutes()),
                'forms' => count($this->formRegistry->getForms()),
                'relations' => count($this->relationRegistry->getRelations()),
                'probes' => count($this->probeRegistry->getProbes()),
                'actionableResources' => $actionable,
                'resourcesWithoutActions' => $missingActions,
            ],
            'cruding' => [
                'available' => $this->crudingRouteBridge->isAvailable(),
                'mode' => $this->crudingRouteBridge->isAvailable() ? 'connected' : 'not installed or no resolver service',
            ],
            'modes' => $modeCounts,
            'resources' => $resourceRows,
        ];
    }
}
