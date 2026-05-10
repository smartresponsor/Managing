<?php

declare(strict_types=1);

namespace App\Managing\Service\Route;

use App\Managing\ServiceInterface\Route\ManageRouteUrlResolverInterface;
use App\Managing\Value\ManageRouteDefinition;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class ManageRouteUrlResolver implements ManageRouteUrlResolverInterface
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function resolveUrl(ManageRouteDefinition $route): ?string
    {
        if (!$route->enabled) {
            return null;
        }

        try {
            return $this->urlGenerator->generate($route->routeName);
        } catch (RouteNotFoundException|MissingMandatoryParametersException) {
            return null;
        }
    }
}
