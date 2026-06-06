<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Administration;

use App\Managing\Value\Administration\RollingBackedManagingAccessReadinessReport;

/**
 * Provides the activation checklist for Rolling-backed Managing field access.
 */
interface RollingBackedManagingAccessReadinessProviderInterface
{
    public function report(): RollingBackedManagingAccessReadinessReport;
}
