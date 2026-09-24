<?php

declare(strict_types=1);

namespace App\Managing\Repository\Crud;

use App\Managing\RepositoryInterface\Crud\ManagePublicationRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

final readonly class ManageDoctrinePublicationRepository implements ManagePublicationRepositoryInterface
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    /** @param class-string<object> $entityFqcn */
    public function find(string $entityFqcn, mixed $identifier): ?object
    {
        $entity = $this->managerForClass($entityFqcn)->getRepository($entityFqcn)->find($identifier);

        return is_object($entity) ? $entity : null;
    }

    /** @param class-string<object> $entityFqcn */
    public function flush(string $entityFqcn): void
    {
        $this->managerForClass($entityFqcn)->flush();
    }

    /** @param class-string<object> $entityFqcn */
    private function managerForClass(string $entityFqcn): ObjectManager
    {
        $manager = $this->managerRegistry->getManagerForClass($entityFqcn);
        if (null === $manager) {
            throw new \LogicException(sprintf('No Doctrine manager is configured for %s.', $entityFqcn));
        }

        return $manager;
    }
}
