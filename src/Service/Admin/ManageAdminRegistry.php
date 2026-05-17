<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminProviderInterface;
use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageContributionFilterInterface;
use App\Managing\Value\ManageCrudResourceDefinition;

final class ManageAdminRegistry implements ManageAdminRegistryInterface
{
    /** @var array<string, ManageAdminProviderInterface> */
    private array $providers = [];

    public function __construct(private readonly ManageContributionFilterInterface $contributionFilter)
    {
    }

    public function addProvider(ManageAdminProviderInterface $provider): void
    {
        $this->providers[$provider->getComponent()->key] = $provider;
    }

    public function getProviders(): array
    {
        return $this->contributionFilter->filterAndSortProviders(array_values($this->providers));
    }

    /**
     * @return list<ManageCrudResourceDefinition>
     */
    public function getCrudResources(): array
    {
        $resources = [];

        foreach ($this->getProviders() as $provider) {
            foreach ($provider->getCrudResources() as $resource) {
                if ($this->contributionFilter->isCrudResourceEnabled($resource)) {
                    $resources[] = $resource;
                }
            }
        }

        return $resources;
    }
}
