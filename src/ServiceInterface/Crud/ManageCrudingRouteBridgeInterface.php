<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Crud;

use App\Managing\Value\ManageCrudResourceDefinition;

interface ManageCrudingRouteBridgeInterface
{
    public function isAvailable(): bool;

    /**
     * @return array<string, string>
     */
    public function buildActionUrls(ManageCrudResourceDefinition $resource): array;
}
