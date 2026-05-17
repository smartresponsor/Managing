<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Admin;

use App\Managing\Value\ManageComponentDefinition;
use App\Managing\Value\ManageCrudResourceDefinition;

interface ManageContributionFilterInterface
{
    public function isComponentEnabled(string $componentKey): bool;

    public function isCrudResourceEnabled(ManageCrudResourceDefinition $resource): bool;

    /**
     * @param list<ManageAdminProviderInterface> $providers
     *
     * @return list<ManageAdminProviderInterface>
     */
    public function filterAndSortProviders(array $providers): array;

    /**
     * @param list<ManageComponentDefinition> $components
     *
     * @return list<ManageComponentDefinition>
     */
    public function sortComponents(array $components): array;
}
