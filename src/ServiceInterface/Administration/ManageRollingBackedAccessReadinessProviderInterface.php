<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Administration;

use App\Managing\Value\Administration\ManageRollingBackedAccessReadinessReport;

/**
 * Provides the activation checklist for Rolling-backed Managing field access.
 */
interface ManageRollingBackedAccessReadinessProviderInterface
{
    public function report(): ManageRollingBackedAccessReadinessReport;
}
