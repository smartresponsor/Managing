<?php

declare(strict_types=1);

namespace App\Managing\Service\Route;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageContributionFilterInterface;
use App\Managing\ServiceInterface\Route\ManageRouteRegistryInterface;

final readonly class ManageRouteRegistry implements ManageRouteRegistryInterface
{
    public function __construct(
        private ManageAdminRegistryInterface $adminRegistry,
        private ManageContributionFilterInterface $contributionFilter,
    ) {
    }

    public function getRoutes(): array
    {
        $routes = [];

        foreach ($this->adminRegistry->getProviders() as $provider) {
            foreach ($provider->getRoutes() as $route) {
                if ($this->contributionFilter->isRouteEnabled($route)) {
                    $routes[] = $route;
                }
            }
        }

        return $routes;
    }
}
