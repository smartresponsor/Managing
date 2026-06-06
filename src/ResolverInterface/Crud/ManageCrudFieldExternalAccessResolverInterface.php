<?php

declare(strict_types=1);

namespace App\Managing\ResolverInterface\Crud;

use App\Managing\Value\Crud\ManageCrudFieldAccessContext;
use App\Managing\Value\Crud\ManageCrudFieldExternalAccessDecision;

interface ManageCrudFieldExternalAccessResolverInterface
{
    public function decisionFor(ManageCrudFieldAccessContext $context): ManageCrudFieldExternalAccessDecision;
}
