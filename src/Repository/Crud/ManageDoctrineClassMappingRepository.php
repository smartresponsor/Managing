<?php

declare(strict_types=1);

namespace App\Managing\Repository\Crud;

use App\Managing\RepositoryInterface\Crud\ManageDoctrineClassMappingRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

final readonly class ManageDoctrineClassMappingRepository implements ManageDoctrineClassMappingRepositoryInterface
{
    public function __construct(private ?ManagerRegistry $managerRegistry = null)
    {
    }

    public function isAvailable(): bool
    {
        return null !== $this->managerRegistry;
    }

    public function hasManagerForClass(string $entityFqcn): bool
    {
        if (null === $this->managerRegistry) {
            return false;
        }

        return null !== $this->managerRegistry->getManagerForClass($entityFqcn);
    }
}
