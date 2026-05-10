<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Admin;

use App\Managing\Value\ManageComponentDefinition;
use App\Managing\Value\ManageCrudResourceDefinition;
use App\Managing\Value\ManageFormDefinition;
use App\Managing\Value\ManageProbeDefinition;
use App\Managing\Value\ManageRelationDefinition;
use App\Managing\Value\ManageRouteDefinition;

interface ManageContributionFilterInterface
{
    public function isComponentEnabled(string $componentKey): bool;

    public function isCrudResourceEnabled(ManageCrudResourceDefinition $resource): bool;

    public function isRouteEnabled(ManageRouteDefinition $route): bool;

    public function isFormEnabled(ManageFormDefinition $form): bool;

    public function isProbeEnabled(ManageProbeDefinition $probe): bool;

    public function isRelationEnabled(ManageRelationDefinition $relation): bool;

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
