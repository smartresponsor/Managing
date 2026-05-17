<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageMenuBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

final class ManageMenuBuilder implements ManageMenuBuilderInterface
{
    /** @var list<string> */
    private array $menuComponents;
    /** @var list<string> */
    private array $menuExcludedComponents;

    public function __construct(
        private readonly ManageAdminRegistryInterface $adminRegistry,
        array|object $menuComponents = [],
        array $menuExcludedComponents = [],
    ) {
        $this->menuComponents = is_array($menuComponents) ? array_values(array_filter($menuComponents, 'is_string')) : [];
        $this->menuExcludedComponents = array_values(array_filter(array_map('strval', $menuExcludedComponents), static fn (string $value): bool => '' !== $value));
    }

    public function buildMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Manage', 'fa fa-home', '/manage');

        $components = [];
        foreach ($this->adminRegistry->getCrudResources() as $resource) {
            if (!$this->isAllowedComponent($resource->componentKey) || $this->isExcludedComponent($resource->componentKey)) {
                continue;
            }

            $components[$resource->componentKey][] = $resource;
        }

        foreach ($this->orderedComponentKeys(array_keys($components)) as $componentKey) {
            if ([] === $components[$componentKey]) {
                continue;
            }

            yield MenuItem::linkToUrl(
                $this->resolveComponentLabel($componentKey),
                'fa fa-database',
                sprintf('/manage/%s', $componentKey),
            );
        }
    }

    private function isExcludedComponent(string $componentKey): bool
    {
        return in_array($componentKey, $this->menuExcludedComponents, true);
    }

    private function isAllowedComponent(string $componentKey): bool
    {
        return [] === $this->menuComponents || in_array($componentKey, $this->menuComponents, true);
    }

    /**
     * @param list<string> $componentKeys
     *
     * @return list<string>
     */
    private function orderedComponentKeys(array $componentKeys): array
    {
        $componentKeys = array_values(array_unique($componentKeys));
        $position = array_flip($this->menuComponents);

        usort(
            $componentKeys,
            static function (string $left, string $right) use ($position): int {
                $leftPosition = $position[$left] ?? PHP_INT_MAX;
                $rightPosition = $position[$right] ?? PHP_INT_MAX;

                if ($leftPosition !== $rightPosition) {
                    return $leftPosition <=> $rightPosition;
                }

                return $left <=> $right;
            },
        );

        return $componentKeys;
    }

    private function resolveComponentLabel(string $componentKey): string
    {
        $value = str_replace(['_', '-'], ' ', $componentKey);

        return ucwords($value);
    }
}
