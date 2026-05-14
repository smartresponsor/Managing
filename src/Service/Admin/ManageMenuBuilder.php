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
    ) {
    }

    public function buildMenuItems(): iterable
    {
        foreach ($this->adminRegistry->getComponents() as $component) {
            yield MenuItem::linkToRoute($component->label, 'fa fa-cube', 'manage_component_detail', [
                'componentKey' => $component->key,
            ]);
        }
    }
}
