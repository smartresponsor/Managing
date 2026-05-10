<?php

declare(strict_types=1);

namespace App\Managing\Service\Resource;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageContributionFilterInterface;
use App\Managing\ServiceInterface\Resource\ManageResourceRegistryInterface;

final readonly class ManageResourceRegistry implements ManageResourceRegistryInterface
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
                if ($this->contributionFilter->isCrudResourceEnabled($resource)) {
                    $resources[] = $resource;
                }
            }
        }

        return $resources;
    }
}
