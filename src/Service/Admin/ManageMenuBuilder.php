<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageMenuBuilderInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudResourceRegistryInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

final readonly class ManageMenuBuilder implements ManageMenuBuilderInterface
{
    public function __construct(
        private readonly ManageCrudResourceRegistryInterface $crudResourceRegistry,
        private array $leftMenu = [],
        private array $menuExcludedComponents = [],
    ) {
    }

    public function buildMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Manage', 'fa fa-home');

        $availableComponents = [];
        foreach ($this->crudResourceRegistry->getCrudResources() as $resource) {
            $availableComponents[$resource->componentKey] = true;
        }

        foreach ($this->orderedComponentKeys($this->leftMenu) as $componentKey) {
            if ($this->isExcludedComponent($componentKey) || !isset($availableComponents[$componentKey])) {
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

    /**
     * @param list<string> $componentKeys
     *
     * @return list<string>
     */
    private function orderedComponentKeys(array $componentKeys): array
    {
        $componentKeys = array_values(array_unique(array_filter($componentKeys, static fn (mixed $value): bool => is_string($value) && '' !== $value)));
        $position = array_flip($this->leftMenu);

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
        return ucwords(str_replace(['_', '-'], ' ', $componentKey));
    }
}
