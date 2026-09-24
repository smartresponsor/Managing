<?php

declare(strict_types=1);

namespace App\Managing\RepositoryInterface\Crud;

interface ManagePublicationRepositoryInterface
{
    /** @param class-string<object> $entityFqcn */
    public function find(string $entityFqcn, mixed $identifier): ?object;

    /** @param class-string<object> $entityFqcn */
    public function flush(string $entityFqcn): void;
}
