<?php

declare(strict_types=1);

namespace App\Managing\Resolver\Crud;

use App\Managing\Factory\Crud\ManageCrudFieldVisibilityExplanationStepFactory;
use App\Managing\ResolverInterface\Crud\ManageCrudFieldExternalAccessResolverInterface;
use App\Managing\ResolverInterface\Crud\ManageCrudFieldUserProfileResolverInterface;
use App\Managing\ResolverInterface\Crud\ManageCrudFieldVisibilityExplanationResolverInterface;
use App\Managing\Value\Crud\ManageCrudFieldAccessContext;
use App\Managing\Value\Crud\ManageCrudFieldDefinition;
use App\Managing\Value\Crud\ManageCrudFieldExternalAccessDecision;
use App\Managing\Value\Crud\ManageCrudFieldVisibilityDecision;
use App\Managing\Value\Crud\ManageCrudFieldVisibilityExplanation;
use App\Managing\Value\Crud\ManageCrudFieldVisibilityExplanationStep;
use App\Managing\Value\Crud\ManageCrudFieldVisibilityRuleSet;

final class ManageCrudFieldVisibilityExplanationResolver implements ManageCrudFieldVisibilityExplanationResolverInterface
{
    private readonly ManageCrudFieldVisibilityRuleSet $ruleSet;
    private readonly ManageCrudFieldVisibilityExplanationStepFactory $stepFactory;

    /** @param array<string, mixed> $fieldVisibilityConfig */
    public function __construct(
        array $fieldVisibilityConfig = [],
        private readonly ?ManageCrudFieldExternalAccessResolverInterface $externalAccessResolver = null,
        private readonly ?ManageCrudFieldUserProfileResolverInterface $userProfileResolver = null,
        ?ManageCrudFieldVisibilityExplanationStepFactory $stepFactory = null,
    ) {
        $this->ruleSet = ManageCrudFieldVisibilityRuleSet::fromArray($fieldVisibilityConfig);
        $this->stepFactory = $stepFactory ?? new ManageCrudFieldVisibilityExplanationStepFactory();
    }

    public function explainFor(ManageCrudFieldDefinition $definition, string $pageName, ?string $subjectIdentifier = null): ManageCrudFieldVisibilityExplanation
    {
        $steps = [];
        if (!$definition->isAvailableOn($pageName)) {
            $decision = new ManageCrudFieldVisibilityDecision($definition->fieldName, false, false, ManageCrudFieldVisibilityDecision::SOURCE_SYSTEM, 'field_not_available_on_page');
            $steps[] = $this->stepFactory->create(
                $decision->source,
                ManageCrudFieldVisibilityExplanationStep::EFFECT_DENY,
                $decision->reason,
                true,
                axis: ManageCrudFieldVisibilityExplanationStep::AXIS_AVAILABILITY,
            );

            return new ManageCrudFieldVisibilityExplanation($definition, $pageName, $subjectIdentifier, $decision, $steps);
        }

        $configuredDecision = $this->ruleSet->decisionFor($definition, $pageName);
        if (null !== $configuredDecision) {
            $steps[] = $this->stepFactory->fromVisibilityDecision($configuredDecision, !$configuredDecision->accessAllowed);
            if (!$configuredDecision->accessAllowed) {
                return new ManageCrudFieldVisibilityExplanation($definition, $pageName, $subjectIdentifier, $configuredDecision, $steps);
            }
        } else {
            $steps[] = $this->stepFactory->create(ManageCrudFieldVisibilityDecision::SOURCE_SYSTEM, ManageCrudFieldVisibilityExplanationStep::EFFECT_ABSTAIN, 'backend_visibility_rule_not_configured');
        }

        $externalDecision = $this->externalDecisionFor($definition, $pageName, $subjectIdentifier);
        $steps[] = $this->stepFactory->fromExternalDecision($externalDecision, $definition->permissionKey);
        if ($externalDecision->denies()) {
            $decision = new ManageCrudFieldVisibilityDecision($definition->fieldName, false, false, $this->visibilitySourceFor($externalDecision), $this->externalAccessDenyReason($externalDecision));

            return new ManageCrudFieldVisibilityExplanation($definition, $pageName, $subjectIdentifier, $decision, $steps);
        }

        $baseDecision = $configuredDecision ?? $this->defaultDecisionFor($definition);
        if (null === $configuredDecision) {
            $steps[] = $this->stepFactory->fromVisibilityDecision($baseDecision);
        }
        $finalDecision = $this->applyUserProfileDecision($definition, $pageName, $subjectIdentifier, $baseDecision, $steps);

        return new ManageCrudFieldVisibilityExplanation($definition, $pageName, $subjectIdentifier, $finalDecision, $steps);
    }

    /** @param list<ManageCrudFieldVisibilityExplanationStep> $steps */
    private function applyUserProfileDecision(
        ManageCrudFieldDefinition $definition,
        string $pageName,
        ?string $subjectIdentifier,
        ManageCrudFieldVisibilityDecision $baseDecision,
        array &$steps,
    ): ManageCrudFieldVisibilityDecision {
        if (!$baseDecision->accessAllowed || null === $this->userProfileResolver) {
            $steps[] = $this->stepFactory->create(ManageCrudFieldVisibilityDecision::SOURCE_USER_PROFILE, ManageCrudFieldVisibilityExplanationStep::EFFECT_ABSTAIN, 'user_profile_resolver_not_configured');

            return $baseDecision;
        }

        $profileDecision = $this->userProfileResolver->decisionFor($definition, $pageName, $subjectIdentifier);
        if (null === $profileDecision) {
            $steps[] = $this->stepFactory->create(ManageCrudFieldVisibilityDecision::SOURCE_USER_PROFILE, ManageCrudFieldVisibilityExplanationStep::EFFECT_ABSTAIN, 'user_profile_rule_not_configured');

            return $baseDecision;
        }

        if (!$profileDecision->visible && !$this->canUserProfileHide($definition, $pageName)) {
            $steps[] = $this->stepFactory->create(ManageCrudFieldVisibilityDecision::SOURCE_USER_PROFILE, ManageCrudFieldVisibilityExplanationStep::EFFECT_IGNORED, 'user_profile_hide_not_allowed');

            return $baseDecision;
        }

        $decision = new ManageCrudFieldVisibilityDecision($definition->fieldName, true, $profileDecision->visible, ManageCrudFieldVisibilityDecision::SOURCE_USER_PROFILE, $profileDecision->reason);
        $steps[] = $this->stepFactory->fromVisibilityDecision($decision);

        return $decision;
    }

    private function defaultDecisionFor(ManageCrudFieldDefinition $definition): ManageCrudFieldVisibilityDecision
    {
        return new ManageCrudFieldVisibilityDecision(
            $definition->fieldName,
            true,
            $definition->defaultVisible,
            ManageCrudFieldVisibilityDecision::SOURCE_SYSTEM,
            $definition->defaultVisible ? 'field_default_visible' : 'field_default_hidden',
        );
    }

    private function canUserProfileHide(ManageCrudFieldDefinition $definition, string $pageName): bool
    {
        if (!$definition->hideable) {
            return false;
        }

        return !$definition->requiredOnForm || !in_array($pageName, [ManageCrudFieldAccessContext::PAGE_NEW, ManageCrudFieldAccessContext::PAGE_EDIT], true);
    }

    private function externalDecisionFor(ManageCrudFieldDefinition $definition, string $pageName, ?string $subjectIdentifier): ManageCrudFieldExternalAccessDecision
    {
        if (null === $this->externalAccessResolver || null === $definition->permissionKey) {
            return ManageCrudFieldExternalAccessDecision::abstain(reason: 'external_field_access_resolver_not_configured');
        }

        return $this->externalAccessResolver->decisionFor(new ManageCrudFieldAccessContext(
            componentKey: $definition->componentKey,
            resourceClass: $definition->resourceClass,
            fieldName: $definition->fieldName,
            pageName: $pageName,
            operation: 'view',
            subjectIdentifier: $subjectIdentifier,
            attributes: ['permission' => $definition->permissionKey, 'fieldType' => $definition->fieldType, 'sensitive' => $definition->sensitive, 'hideable' => $definition->hideable, 'requiredOnForm' => $definition->requiredOnForm],
        ));
    }

    private function externalAccessDenyReason(ManageCrudFieldExternalAccessDecision $decision): string
    {
        if (ManageCrudFieldExternalAccessDecision::SOURCE_ROLLING === $decision->source) {
            return 'rolling_field_value_access_denied';
        }

        return $decision->reason ?? 'external_field_access_denied';
    }

    private function visibilitySourceFor(ManageCrudFieldExternalAccessDecision $decision): string
    {
        return match ($decision->source) {
            ManageCrudFieldExternalAccessDecision::SOURCE_ROLLING => ManageCrudFieldVisibilityDecision::SOURCE_ROLLING,
            default => ManageCrudFieldVisibilityDecision::SOURCE_SYSTEM,
        };
    }
}
