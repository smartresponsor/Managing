<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminProviderInterface;
use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageContributionFilterInterface;
use App\Managing\Value\ManageComponentDefinition;
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

    public function getComponents(): array
    {
        $components = [];

        foreach ($this->getProviders() as $provider) {
            $component = $provider->getComponent();
            $components[$component->key] = $component;

            if (!method_exists($provider, 'getConfiguredComponents')) {
                continue;
            }

            foreach ($provider->getConfiguredComponents() as $configuredComponent) {
                if ($configuredComponent instanceof ManageComponentDefinition) {
                    $components[$configuredComponent->key] = $configuredComponent;
                }
            }
        }

        return $this->contributionFilter->sortComponents(array_values($components));
    }

    /**
     * @return list<ManageCrudResourceDefinition>
     */
    public function getCrudResources(): array
    {
        $resources = [];

        foreach ($this->getProviders() as $provider) {
            foreach ($provider->getCrudResources() as $resource) {
                if ($resource instanceof ManageCrudResourceDefinition && $this->contributionFilter->isCrudResourceEnabled($resource)) {
                    $resources[] = $resource;
                }
            }
        }

        usort(
            $resources,
            static fn (ManageCrudResourceDefinition $left, ManageCrudResourceDefinition $right): int => [$left->componentKey, $left->label, $left->resourceKey] <=> [$right->componentKey, $right->label, $right->resourceKey],
        );

        return $resources;
    }
}
