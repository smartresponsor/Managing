<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\Controller\Crud\ManageProbeRunCrudController;
use App\Managing\Controller\Crud\ManageSnapshotCrudController;
use App\Managing\Entity\ManageProbeRun;
use App\Managing\Entity\ManageSnapshot;
use App\Managing\ServiceInterface\Admin\ManageAdminProviderInterface;
use App\Managing\Value\ManageComponentDefinition;
use App\Managing\Value\ManageCrudResourceDefinition;
use App\Managing\Value\ManageFormDefinition;
use App\Managing\Value\ManageProbeDefinition;
use App\Managing\Value\ManageRelationDefinition;
use App\Managing\Value\ManageRouteDefinition;

final readonly class ManageSelfAdminProvider implements ManageAdminProviderInterface
{
    public function getComponent(): ManageComponentDefinition
    {
        return new ManageComponentDefinition(
            key: 'managing',
            label: 'Managing',
            description: 'Native EasyAdmin management console for Smart Responsor components.',
        );
    }

    public function getCrudResources(): iterable
    {
        yield new ManageCrudResourceDefinition(
            componentKey: 'managing',
            resourceKey: 'manage_probe_run',
            label: 'Manage probe runs',
            entityClass: ManageProbeRun::class,
            crudControllerClass: ManageProbeRunCrudController::class,
            menuGroup: 'Managing state',
            mode: ManageCrudResourceDefinition::MODE_EASYADMIN,
            resourcePath: 'manage/probe-runs',
        );

        yield new ManageCrudResourceDefinition(
            componentKey: 'managing',
            resourceKey: 'manage_snapshot',
            label: 'Manage snapshots',
            entityClass: ManageSnapshot::class,
            crudControllerClass: ManageSnapshotCrudController::class,
            menuGroup: 'Managing state',
            mode: ManageCrudResourceDefinition::MODE_EASYADMIN,
            resourcePath: 'manage/snapshots',
        );
    }

    public function getRoutes(): iterable
    {
        yield new ManageRouteDefinition('managing', 'manage_dashboard', 'Dashboard', 'admin', 'Workbench');
        yield new ManageRouteDefinition('managing', 'manage_status', 'Status', 'inspection', 'Workbench');
        yield new ManageRouteDefinition('managing', 'manage_workbench', 'Workbench index', 'inspection', 'Workbench');
        yield new ManageRouteDefinition('managing', 'manage_dashboard_components_index', 'Components', 'inspection', 'Workbench');
        yield new ManageRouteDefinition('managing', 'manage_resources', 'Resources', 'inspection', 'Workbench');
        yield new ManageRouteDefinition('managing', 'manage_routes', 'Routes', 'inspection', 'Workbench');
        yield new ManageRouteDefinition('managing', 'manage_forms', 'Forms', 'inspection', 'Workbench');
        yield new ManageRouteDefinition('managing', 'manage_relations', 'Relations', 'inspection', 'Workbench');
        yield new ManageRouteDefinition('managing', 'manage_probes', 'Probes', 'inspection', 'Workbench');
    }

    public function getForms(): iterable
    {
        yield new ManageFormDefinition(
            componentKey: 'managing',
            formKey: 'manage_probe_run',
            label: 'Manage probe run form',
            formTypeClass: \App\Managing\Form\ManageProbeRunType::class,
            resourceKey: 'manage_probe_run',
            description: 'Symfony Form contract for internal Manage probe run records.',
            menuGroup: 'Managing forms',
        );
    }

    public function getRelations(): iterable
    {
        yield new ManageRelationDefinition(
            componentKey: 'managing',
            relationKey: 'manage_probe_run_to_manage_snapshot',
            label: 'Probe run snapshot context',
            sourceResourceKey: 'manage_probe_run',
            targetResourceKey: 'manage_snapshot',
            kind: 'inspection_reference',
            description: 'Internal inspection relation used by the admin workbench to show how probe output can be attached to management snapshots.',
            menuGroup: 'Managing relations',
        );
    }

    public function getProbes(): iterable
    {
        yield new ManageProbeDefinition(
            componentKey: 'managing',
            probeKey: 'manage_canon_guard',
            label: 'Manage canon guard',
            description: 'Checks that /manage stays EasyAdmin-native and that Managing symbols use the Manage prefix.',
        );
    }
}
