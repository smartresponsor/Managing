<?php

declare(strict_types=1);

namespace App\Managing\ResolverInterface\Crud;

use App\Managing\Value\Crud\ManageCrudFieldDefinition;
use App\Managing\Value\Crud\ManageCrudFieldVisibilityExplanation;

interface ManageCrudFieldVisibilityExplanationResolverInterface
{
    public function explainFor(ManageCrudFieldDefinition $definition, string $pageName, ?string $subjectIdentifier = null): ManageCrudFieldVisibilityExplanation;
}
