<?php

declare(strict_types=1);

namespace App\Managing\Registry\Crud;

use App\Managing\FilterInterface\Admin\ManageContributionFilterInterface;
use App\Managing\RegistryInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\RegistryInterface\Crud\ManageCrudResourceRegistryInterface;
use App\Managing\Value\ManageCrudResourceDefinition;

final class ManageCrudResourceRegistry implements ManageCrudResourceRegistryInterface
{
    /** @var list<ManageCrudResourceDefinition>|null */
    private ?array $cachedCrudResources = null;

    public function __construct(
        private ManageAdminRegistryInterface $adminRegistry,
        private ManageContributionFilterInterface $contributionFilter,
    ) {
    }

    public function getCrudResources(): array
    {
        if (null !== $this->cachedCrudResources) {
            return $this->cachedCrudResources;
        }

        $resources = [];

        foreach ($this->adminRegistry->getProviders() as $provider) {
            foreach ($provider->getCrudResources() as $resource) {
                if ($resource instanceof ManageCrudResourceDefinition && $this->contributionFilter->isCrudResourceEnabled($resource)) {
                    $resources[] = $resource;
                }
            }
        }

        return $this->cachedCrudResources = $resources;
    }
}
