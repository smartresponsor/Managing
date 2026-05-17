<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageContributionFilterInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudResourceRegistryInterface;
use App\Managing\Value\ManageCrudResourceDefinition;

final readonly class ManageCrudResourceRegistry implements ManageCrudResourceRegistryInterface
{
    public function __construct(
        private ManageAdminRegistryInterface $adminRegistry,
        private ManageContributionFilterInterface $contributionFilter,
    ) {
    }

    public function getCrudResources(): array
    {
        $resources = [];

        foreach ($this->adminRegistry->getProviders() as $provider) {
            foreach ($provider->getCrudResources() as $resource) {
                if ($resource instanceof ManageCrudResourceDefinition && $this->contributionFilter->isCrudResourceEnabled($resource)) {
                    $resources[] = $resource;
                }
            }
        }

        return $resources;
    }
}
