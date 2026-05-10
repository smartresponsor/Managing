<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Admin;

interface ManageAdminNavigationBuilderInterface
{
    /**
     * @return list<array{label: string, route: string, icon: string, count: int|null, description: string}>
     */
    public function buildPrimaryNavigation(): array;
}
