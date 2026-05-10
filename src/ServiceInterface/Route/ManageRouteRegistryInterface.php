<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Route;

use App\Managing\Value\ManageRouteDefinition;

interface ManageRouteRegistryInterface
{
    /**
     * @return list<ManageRouteDefinition>
     */
    public function getRoutes(): array;
}
