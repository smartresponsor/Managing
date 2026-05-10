<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Crud;

use App\Managing\Value\ManageCrudResourceDefinition;

interface ManageCrudResourceRegistryInterface
{
    /**
     * @return list<ManageCrudResourceDefinition>
     */
    public function getCrudResources(): array;
}
