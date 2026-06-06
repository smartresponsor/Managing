<?php

declare(strict_types=1);

namespace App\Managing\Registry\Admin;

use App\Managing\FilterInterface\Admin\ManageContributionFilterInterface;
use App\Managing\ProviderInterface\Admin\ManageAdminProviderInterface;
use App\Managing\RegistryInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\Value\ManageCrudResourceDefinition;

final class ManageAdminRegistry implements ManageAdminRegistryInterface
{
    /** @var array<string, ManageAdminProviderInterface> */
    private array $providers = [];

    /** @var list<ManageAdminProviderInterface>|null */
    private ?array $cachedProviders = null;

    /** @var list<ManageCrudResourceDefinition>|null */
    private ?array $cachedCrudResources = null;

    public function __construct(private readonly ManageContributionFilterInterface $contributionFilter)
    {
    }

    public function addProvider(ManageAdminProviderInterface $provider): void
    {
        $this->providers[$provider->getComponent()->key] = $provider;
        $this->cachedProviders = null;
        $this->cachedCrudResources = null;
    }

    public function getProviders(): array
    {
        if (null !== $this->cachedProviders) {
            return $this->cachedProviders;
        }

        return $this->cachedProviders = $this->contributionFilter->filterAndSortProviders(array_values($this->providers));
    }

    /**
     * @return list<ManageCrudResourceDefinition>
     */
    public function getCrudResources(): array
    {
        if (null !== $this->cachedCrudResources) {
            return $this->cachedCrudResources;
        }

        $resources = [];

        foreach ($this->getProviders() as $provider) {
            foreach ($provider->getCrudResources() as $resource) {
                if ($this->contributionFilter->isCrudResourceEnabled($resource)) {
                    $resources[] = $resource;
                }
            }
        }

        return $this->cachedCrudResources = $resources;
    }
}
