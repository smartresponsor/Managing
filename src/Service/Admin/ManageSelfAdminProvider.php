<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminProviderInterface;
use App\Managing\Value\ManageComponentDefinition;

final readonly class ManageSelfAdminProvider implements ManageAdminProviderInterface
{
    public function getComponent(): ManageComponentDefinition
    {
        return new ManageComponentDefinition(
            key: 'managing',
            label: 'Managing',
            description: 'Native EasyAdmin content console for Smart Responsor components.',
        );
    }

    public function getCrudResources(): iterable
    {
        return [];
    }
}
