<?php

declare(strict_types=1);

namespace App\Managing\RepositoryInterface\Crud;

interface ManageDoctrineClassMappingRepositoryInterface
{
    public function isAvailable(): bool;

    public function hasManagerForClass(string $entityFqcn): bool;
}
