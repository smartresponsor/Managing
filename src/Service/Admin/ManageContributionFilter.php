<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminProviderInterface;
use App\Managing\ServiceInterface\Admin\ManageContributionFilterInterface;
use App\Managing\Value\ManageComponentDefinition;
use App\Managing\Value\ManageCrudResourceDefinition;
use App\Managing\Value\ManageFormDefinition;
use App\Managing\Value\ManageProbeDefinition;
use App\Managing\Value\ManageRelationDefinition;
use App\Managing\Value\ManageRouteDefinition;

final readonly class ManageContributionFilter implements ManageContributionFilterInterface
{
    /**
     * @param list<string> $enabledComponents
     * @param list<string> $disabledResources
     * @param list<string> $menuOrder
     */
    public function __construct(
        private array $enabledComponents = [],
        private array $disabledResources = [],
        private array $menuOrder = [],
    ) {
    }

    public function isComponentEnabled(string $componentKey): bool
    {
        return [] === $this->enabledComponents || in_array($componentKey, $this->enabledComponents, true);
    }

    public function isCrudResourceEnabled(ManageCrudResourceDefinition $resource): bool
    {
        if (!$resource->enabled || !$this->isComponentEnabled($resource->componentKey)) {
            return false;
        }

        return !$this->isDisabledResource($resource->componentKey.'.'.$resource->resourceKey)
            && !$this->isDisabledResource($resource->resourceKey);
    }

    public function isRouteEnabled(ManageRouteDefinition $route): bool
    {
        return $route->enabled && $this->isComponentEnabled($route->componentKey);
    }

    public function isFormEnabled(ManageFormDefinition $form): bool
    {
        if (!$form->enabled || !$this->isComponentEnabled($form->componentKey)) {
            return false;
        }

        return !$this->isDisabledResource($form->componentKey.'.'.$form->formKey)
            && !$this->isDisabledResource($form->formKey);
    }

    public function isProbeEnabled(ManageProbeDefinition $probe): bool
    {
        return $probe->enabled && $this->isComponentEnabled($probe->componentKey);
    }

    public function isRelationEnabled(ManageRelationDefinition $relation): bool
    {
        if (!$relation->enabled || !$this->isComponentEnabled($relation->componentKey)) {
            return false;
        }

        return !$this->isDisabledResource($relation->componentKey.'.'.$relation->relationKey)
            && !$this->isDisabledResource($relation->relationKey);
    }

    public function filterAndSortProviders(array $providers): array
    {
        $filtered = array_values(array_filter(
            $providers,
            fn (ManageAdminProviderInterface $provider): bool => $this->isProviderVisible($provider),
        ));

        usort(
            $filtered,
            fn (ManageAdminProviderInterface $left, ManageAdminProviderInterface $right): int => $this->compareComponentKeys($left->getComponent()->key, $right->getComponent()->key),
        );

        return $filtered;
    }

    public function sortComponents(array $components): array
    {
        $filtered = array_values(array_filter(
            $components,
            fn (ManageComponentDefinition $component): bool => $this->isComponentEnabled($component->key),
        ));

        usort(
            $filtered,
            fn (ManageComponentDefinition $left, ManageComponentDefinition $right): int => $this->compareComponentKeys($left->key, $right->key),
        );

        return $filtered;
    }

    private function isProviderVisible(ManageAdminProviderInterface $provider): bool
    {
        if ($this->isComponentEnabled($provider->getComponent()->key)) {
            return true;
        }

        foreach ($provider->getCrudResources() as $resource) {
            if ($this->isCrudResourceEnabled($resource)) {
                return true;
            }
        }

        foreach ($provider->getRoutes() as $route) {
            if ($this->isRouteEnabled($route)) {
                return true;
            }
        }

        foreach ($provider->getForms() as $form) {
            if ($this->isFormEnabled($form)) {
                return true;
            }
        }

        foreach ($provider->getProbes() as $probe) {
            if ($this->isProbeEnabled($probe)) {
                return true;
            }
        }

        return false;
    }

    private function isDisabledResource(string $resourceKey): bool
    {
        return in_array($resourceKey, $this->disabledResources, true);
    }

    private function compareComponentKeys(string $left, string $right): int
    {
        $leftPosition = $this->menuOrderPosition($left);
        $rightPosition = $this->menuOrderPosition($right);

        if ($leftPosition !== $rightPosition) {
            return $leftPosition <=> $rightPosition;
        }

        return $left <=> $right;
    }

    private function menuOrderPosition(string $componentKey): int
    {
        $position = array_search($componentKey, $this->menuOrder, true);

        return false === $position ? PHP_INT_MAX : (int) $position;
    }
}
