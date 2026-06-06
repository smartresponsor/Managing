<?php

declare(strict_types=1);

namespace App\Managing\Resolver\Crud;

use App\Managing\ResolverInterface\Crud\ManageCrudFieldUserProfileResolverInterface;
use App\Managing\Value\Crud\ManageCrudFieldDefinition;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileDecision;

/**
 * Safe fallback personal-profile resolver used when no storage-backed resolver is connected.
 */
final class ManageCrudNullFieldUserProfileResolver implements ManageCrudFieldUserProfileResolverInterface
{
    public function decisionFor(ManageCrudFieldDefinition $definition, string $pageName, ?string $subjectIdentifier = null): ?ManageCrudFieldUserProfileDecision
    {
        return null;
    }
}
