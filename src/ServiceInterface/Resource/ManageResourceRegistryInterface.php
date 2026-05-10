<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Resource;

use App\Managing\Value\ManageCrudResourceDefinition;

interface ManageResourceRegistryInterface
{
    /**
     * @return list<ManageCrudResourceDefinition>
     */
    public function getCrudResources(): array;
}
