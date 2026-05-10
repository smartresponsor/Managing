<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Admin;

use App\Managing\Value\ManageComponentDefinition;

interface ManageAdminRegistryInterface
{
    public function addProvider(ManageAdminProviderInterface $provider): void;

    /**
     * @return list<ManageAdminProviderInterface>
     */
    public function getProviders(): array;

    /**
     * @return list<ManageComponentDefinition>
     */
    public function getComponents(): array;
}
