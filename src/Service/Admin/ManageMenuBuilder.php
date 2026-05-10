<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageContributionFilterInterface;
use App\Managing\ServiceInterface\Admin\ManageMenuBuilderInterface;
use App\Managing\Value\ManageCrudResourceDefinition;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

final readonly class ManageMenuBuilder implements ManageMenuBuilderInterface
{
    public function __construct(
        private ManageAdminRegistryInterface $adminRegistry,
        private ManageContributionFilterInterface $contributionFilter,
    ) {
    }

    public function buildMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Manage dashboard', 'fa fa-home');
        yield MenuItem::section('Workbench');
        yield MenuItem::linkToRoute('Status', 'fa fa-heartbeat', 'manage_status');
        yield MenuItem::linkToRoute('Configuration', 'fa fa-sliders', 'manage_configuration');
        yield MenuItem::linkToRoute('Security', 'fa fa-shield-alt', 'manage_security');
        yield MenuItem::linkToRoute('Workbench index', 'fa fa-sitemap', 'manage_workbench');
        yield MenuItem::linkToRoute('Components', 'fa fa-cubes', 'manage_components');
        yield MenuItem::linkToRoute('Resources', 'fa fa-database', 'manage_resources');
        yield MenuItem::linkToRoute('Routes', 'fa fa-route', 'manage_routes');
        yield MenuItem::linkToRoute('Forms', 'fa fa-list-alt', 'manage_forms');
        yield MenuItem::linkToRoute('Relations', 'fa fa-project-diagram', 'manage_relations');
        yield MenuItem::linkToRoute('Probes', 'fa fa-vial', 'manage_probes');

        foreach ($this->adminRegistry->getProviders() as $provider) {
            $items = [];

            foreach ($provider->getCrudResources() as $resource) {
                if (!$this->contributionFilter->isCrudResourceEnabled($resource)
                    || ManageCrudResourceDefinition::MODE_EASYADMIN !== $resource->mode
                    || null === $resource->crudControllerClass
                ) {
                    continue;
                }

                $items[] = MenuItem::linkToCrud($resource->label, 'fa fa-table', $resource->entityClass)
                    ->setController($resource->crudControllerClass);
            }

            foreach ($provider->getRoutes() as $route) {
                if (!$this->contributionFilter->isRouteEnabled($route)) {
                    continue;
                }

                $items[] = MenuItem::linkToRoute($route->label, 'fa fa-link', $route->routeName);
            }

            if ([] === $items) {
                continue;
            }

            yield MenuItem::section($provider->getComponent()->label);
            yield from $items;
        }
    }
}
