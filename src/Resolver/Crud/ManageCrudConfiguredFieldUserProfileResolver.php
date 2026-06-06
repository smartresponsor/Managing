<?php

declare(strict_types=1);

namespace App\Managing\Resolver\Crud;

use App\Managing\ResolverInterface\Crud\ManageCrudFieldUserProfileResolverInterface;
use App\Managing\Value\Crud\ManageCrudFieldDefinition;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileDecision;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileRuleSet;

/**
 * Config-backed execution seam for personal field view profiles.
 *
 * It models the eventual storage shape while keeping runtime independent from a concrete table,
 * entity manager, or Administering UI implementation.
 */
final class ManageCrudConfiguredFieldUserProfileResolver implements ManageCrudFieldUserProfileResolverInterface
{
    private readonly ManageCrudFieldUserProfileRuleSet $ruleSet;

    /** @param array<string, mixed> $fieldUserProfilesConfig */
    public function __construct(array $fieldUserProfilesConfig = [])
    {
        $this->ruleSet = ManageCrudFieldUserProfileRuleSet::fromArray($fieldUserProfilesConfig);
    }

    public function decisionFor(ManageCrudFieldDefinition $definition, string $pageName, ?string $subjectIdentifier = null): ?ManageCrudFieldUserProfileDecision
    {
        return $this->ruleSet->decisionFor($definition, $pageName, $subjectIdentifier);
    }
}
