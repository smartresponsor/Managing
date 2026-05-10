<?php

declare(strict_types=1);

namespace App\Managing\Service\Route;

use App\Managing\ServiceInterface\Route\ManageRouteDetailBuilderInterface;
use App\Managing\ServiceInterface\Route\ManageRouteRegistryInterface;
use App\Managing\ServiceInterface\Route\ManageRouteUrlResolverInterface;
use App\Managing\Value\ManageRouteDefinition;

final readonly class ManageRouteDetailBuilder implements ManageRouteDetailBuilderInterface
{
    public function __construct(
        private ManageRouteRegistryInterface $routeRegistry,
        private ManageRouteUrlResolverInterface $routeUrlResolver,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buildRouteDetail(string $componentKey, string $routeName): ?array
    {
        $route = $this->findRoute($componentKey, $routeName);

        if (!$route instanceof ManageRouteDefinition) {
            return null;
        }

        return [
            'route' => $route,
            'url' => $this->routeUrlResolver->resolveUrl($route),
            'routable' => null !== $this->routeUrlResolver->resolveUrl($route),
        ];
    }

    private function findRoute(string $componentKey, string $routeName): ?ManageRouteDefinition
    {
        foreach ($this->routeRegistry->getRoutes() as $route) {
            if ($route->componentKey === $componentKey && $route->routeName === $routeName) {
                return $route;
            }
        }

        return null;
    }
}
