<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageMenuBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;

final readonly class ManageMenuBuilder implements ManageMenuBuilderInterface
{
    public function __construct(
        private ManageAdminRegistryInterface $adminRegistry,
        /** @var list<string> */
        private array $menuComponents,
    ) {
    }

    public function buildMenuItems(): iterable
    {
        $componentsByKey = [];
        foreach ($this->adminRegistry->getComponents() as $component) {
            $componentsByKey[$component->key] = $component;
        }

        foreach ($this->menuComponents as $componentKey) {
            if (!isset($componentsByKey[$componentKey])) {
                continue;
            }

            $component = $componentsByKey[$componentKey];
            yield MenuItem::linkToRoute(
                $component->label,
                'fa fa-cube',
                'manage_dashboard_components_detail',
                [
                    'componentKey' => $component->key,
                ]
            );
        }
    }
}
