<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminNavigationBuilderInterface;
use App\Managing\ServiceInterface\Admin\ManageDashboardSummaryBuilderInterface;

final readonly class ManageAdminNavigationBuilder implements ManageAdminNavigationBuilderInterface
{
    public function __construct(
        private ManageDashboardSummaryBuilderInterface $summaryBuilder,
    ) {
    }

    public function buildPrimaryNavigation(): array
    {
        $summary = $this->summaryBuilder->buildSummary();

        return [
            [
                'label' => 'Workbench',
                'route' => 'manage_workbench',
                'icon' => 'fa fa-sitemap',
                'count' => null,
                'description' => 'Consolidated readiness index across all Manage contributions.',
            ],
            [
                'label' => 'Components',
                'route' => 'manage_components',
                'icon' => 'fa fa-cubes',
                'count' => $summary['components'],
                'description' => 'Registered component contributions visible to the admin console.',
            ],
            [
                'label' => 'Resources',
                'route' => 'manage_resources',
                'icon' => 'fa fa-database',
                'count' => $summary['crudResources'],
                'description' => 'CRUD-capable resource definitions and generated admin actions.',
            ],
            [
                'label' => 'Routes',
                'route' => 'manage_routes',
                'icon' => 'fa fa-route',
                'count' => $summary['routes'],
                'description' => 'Explicit standalone routes exposed to the management console.',
            ],
            [
                'label' => 'Forms',
                'route' => 'manage_forms',
                'icon' => 'fa fa-list-alt',
                'count' => $summary['forms'],
                'description' => 'Symfony form contracts registered for inspection.',
            ],
            [
                'label' => 'Relations',
                'route' => 'manage_relations',
                'icon' => 'fa fa-project-diagram',
                'count' => $summary['relations'],
                'description' => 'Declared connections between resources and admin concepts.',
            ],
            [
                'label' => 'Probes',
                'route' => 'manage_probes',
                'icon' => 'fa fa-vial',
                'count' => $summary['probes'],
                'description' => 'Runnable management checks and readiness probes.',
            ],
            [
                'label' => 'Status',
                'route' => 'manage_status',
                'icon' => 'fa fa-heartbeat',
                'count' => null,
                'description' => 'Bridge, registry and action readiness diagnostics.',
            ],
            [
                'label' => 'Configuration',
                'route' => 'manage_configuration',
                'icon' => 'fa fa-sliders',
                'count' => null,
                'description' => 'Active Managing contribution filters and configured entries.',
            ],
        ];
    }
}
