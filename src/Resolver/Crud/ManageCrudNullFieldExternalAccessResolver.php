<?php

declare(strict_types=1);

namespace App\Managing\Resolver\Crud;

use App\Managing\ResolverInterface\Crud\ManageCrudFieldExternalAccessResolverInterface;
use App\Managing\Value\Crud\ManageCrudFieldAccessContext;
use App\Managing\Value\Crud\ManageCrudFieldExternalAccessDecision;

/**
 * Safe fallback used when no Rolling-aware field access provider is installed.
 */
final class ManageCrudNullFieldExternalAccessResolver implements ManageCrudFieldExternalAccessResolverInterface
{
    public function decisionFor(ManageCrudFieldAccessContext $context): ManageCrudFieldExternalAccessDecision
    {
        return ManageCrudFieldExternalAccessDecision::abstain(reason: 'external_field_access_resolver_not_configured');
    }
}
