<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Route;

interface ManageRouteDetailBuilderInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function buildRouteDetail(string $componentKey, string $routeName): ?array;
}
