<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use App\Managing\ServiceInterface\Crud\ManageCrudResourceRegistryInterface;
use App\Managing\ServiceInterface\Resource\ManageResourceRegistryInterface;

final readonly class ManageCrudResourceRegistry implements ManageCrudResourceRegistryInterface
{
    public function __construct(private ManageResourceRegistryInterface $resourceRegistry)
    {
    }

    public function getCrudResources(): array
    {
        return $this->resourceRegistry->getCrudResources();
    }
}
