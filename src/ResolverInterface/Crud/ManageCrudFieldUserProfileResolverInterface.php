<?php

declare(strict_types=1);

namespace App\Managing\ResolverInterface\Crud;

use App\Managing\Value\Crud\ManageCrudFieldDefinition;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileDecision;

interface ManageCrudFieldUserProfileResolverInterface
{
    public function decisionFor(ManageCrudFieldDefinition $definition, string $pageName, ?string $subjectIdentifier = null): ?ManageCrudFieldUserProfileDecision;
}
