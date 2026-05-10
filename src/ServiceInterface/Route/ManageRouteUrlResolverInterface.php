<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Route;

use App\Managing\Value\ManageRouteDefinition;

interface ManageRouteUrlResolverInterface
{
    public function resolveUrl(ManageRouteDefinition $route): ?string;
}
