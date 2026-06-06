<?php

declare(strict_types=1);

namespace App\Managing\ProviderInterface\Admin;

use App\Managing\Value\ManageComponentDefinition;
use App\Managing\Value\ManageCrudResourceDefinition;

interface ManageAdminProviderInterface
{
    public function getComponent(): ManageComponentDefinition;

    /**
     * @return iterable<ManageCrudResourceDefinition>
     */
    public function getCrudResources(): iterable;
}
