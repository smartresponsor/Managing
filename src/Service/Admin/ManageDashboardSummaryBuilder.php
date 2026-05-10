<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageDashboardSummaryBuilderInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudResourceRegistryInterface;
use App\Managing\ServiceInterface\Form\ManageFormRegistryInterface;
use App\Managing\ServiceInterface\Probe\ManageProbeRegistryInterface;
use App\Managing\ServiceInterface\Relation\ManageRelationRegistryInterface;
use App\Managing\ServiceInterface\Route\ManageRouteRegistryInterface;

final readonly class ManageDashboardSummaryBuilder implements ManageDashboardSummaryBuilderInterface
{
    public function __construct(
        private ManageAdminRegistryInterface $adminRegistry,
        private ManageCrudResourceRegistryInterface $crudResourceRegistry,
        private ManageRouteRegistryInterface $routeRegistry,
        private ManageFormRegistryInterface $formRegistry,
        private ManageRelationRegistryInterface $relationRegistry,
        private ManageProbeRegistryInterface $probeRegistry,
    ) {
    }

    public function buildSummary(): array
    {
        return [
            'components' => count($this->adminRegistry->getComponents()),
            'crudResources' => count($this->crudResourceRegistry->getCrudResources()),
            'routes' => count($this->routeRegistry->getRoutes()),
            'forms' => count($this->formRegistry->getForms()),
            'relations' => count($this->relationRegistry->getRelations()),
            'probes' => count($this->probeRegistry->getProbes()),
        ];
    }
}
