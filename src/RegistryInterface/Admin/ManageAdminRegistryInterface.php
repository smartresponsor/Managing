<?php

declare(strict_types=1);

namespace App\Managing\RegistryInterface\Admin;

use App\Managing\ProviderInterface\Admin\ManageAdminProviderInterface;
use App\Managing\Value\ManageCrudResourceDefinition;

interface ManageAdminRegistryInterface
{
    public function addProvider(ManageAdminProviderInterface $provider): void;

    /**
     * @return list<ManageAdminProviderInterface>
     */
    public function getProviders(): array;

    /**
     * @return list<ManageCrudResourceDefinition>
     */
    public function getCrudResources(): array;
}
