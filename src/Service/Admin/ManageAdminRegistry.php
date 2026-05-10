<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminProviderInterface;
use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageContributionFilterInterface;
use App\Managing\Value\ManageComponentDefinition;

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
}
