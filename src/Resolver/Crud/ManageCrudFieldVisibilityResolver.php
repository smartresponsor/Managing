<?php

declare(strict_types=1);

namespace App\Managing\Resolver\Crud;

use App\Managing\ResolverInterface\Crud\ManageCrudFieldExternalAccessResolverInterface;
use App\Managing\ResolverInterface\Crud\ManageCrudFieldUserProfileResolverInterface;
use App\Managing\ResolverInterface\Crud\ManageCrudFieldVisibilityResolverInterface;
use App\Managing\Value\Crud\ManageCrudFieldAccessContext;
use App\Managing\Value\Crud\ManageCrudFieldDefinition;
use App\Managing\Value\Crud\ManageCrudFieldExternalAccessDecision;
use App\Managing\Value\Crud\ManageCrudFieldVisibilityDecision;
use App\Managing\Value\Crud\ManageCrudFieldVisibilityRuleSet;

/**
 * Default Managing-side presentation resolver for discovered CRUD fields.
 *
 * The resolver applies local system/default visibility, backend-configured field
 * visibility, and an optional external access decision seam. External deny wins
 * over presentation preferences, while external allow does not force rendering.
 */
final class ManageCrudFieldVisibilityResolver implements ManageCrudFieldVisibilityResolverInterface
{
    private readonly ManageCrudFieldVisibilityRuleSet $ruleSet;

    /** @param array<string, mixed> $fieldVisibilityConfig */
    public function __construct(
        array $fieldVisibilityConfig = [],
        private readonly ?ManageCrudFieldExternalAccessResolverInterface $externalAccessResolver = null,
        private readonly ?ManageCrudFieldUserProfileResolverInterface $userProfileResolver = null,
    ) {
        $this->ruleSet = ManageCrudFieldVisibilityRuleSet::fromArray($fieldVisibilityConfig);
    }

    public function decisionFor(ManageCrudFieldDefinition $definition, string $pageName, ?string $subjectIdentifier = null): ManageCrudFieldVisibilityDecision
    {
        if (!$definition->isAvailableOn($pageName)) {
            return new ManageCrudFieldVisibilityDecision(
                fieldName: $definition->fieldName,
                accessAllowed: false,
                visible: false,
                source: ManageCrudFieldVisibilityDecision::SOURCE_SYSTEM,
                reason: 'field_not_available_on_page',
            );
        }

        $configuredDecision = $this->ruleSet->decisionFor($definition, $pageName);
        if (null !== $configuredDecision && !$configuredDecision->accessAllowed) {
            return $configuredDecision;
        }

        $externalDecision = $this->externalDecisionFor($definition, $pageName, $subjectIdentifier);
        if ($externalDecision->denies()) {
            return new ManageCrudFieldVisibilityDecision(
                fieldName: $definition->fieldName,
                accessAllowed: false,
                visible: false,
                source: $this->visibilitySourceFor($externalDecision),
                reason: $externalDecision->reason ?? 'external_field_access_denied',
            );
        }

        $baseDecision = $configuredDecision ?? $this->defaultDecisionFor($definition);

        return $this->applyUserProfileDecision($definition, $pageName, $subjectIdentifier, $baseDecision);
    }

    private function defaultDecisionFor(ManageCrudFieldDefinition $definition): ManageCrudFieldVisibilityDecision
    {
        if (!$definition->defaultVisible) {
            return new ManageCrudFieldVisibilityDecision(
                fieldName: $definition->fieldName,
                accessAllowed: true,
                visible: false,
                source: ManageCrudFieldVisibilityDecision::SOURCE_SYSTEM,
                reason: 'field_default_hidden',
            );
        }

        return new ManageCrudFieldVisibilityDecision(
            fieldName: $definition->fieldName,
            accessAllowed: true,
            visible: true,
            source: ManageCrudFieldVisibilityDecision::SOURCE_SYSTEM,
            reason: 'field_default_visible',
        );
    }

    private function applyUserProfileDecision(
        ManageCrudFieldDefinition $definition,
        string $pageName,
        ?string $subjectIdentifier,
        ManageCrudFieldVisibilityDecision $baseDecision,
    ): ManageCrudFieldVisibilityDecision {
        if (!$baseDecision->accessAllowed || null === $this->userProfileResolver) {
            return $baseDecision;
        }

        $profileDecision = $this->userProfileResolver->decisionFor($definition, $pageName, $subjectIdentifier);
        if (null === $profileDecision) {
            return $baseDecision;
        }

        if (!$profileDecision->visible && !$this->canUserProfileHide($definition, $pageName)) {
            return $baseDecision;
        }

        return new ManageCrudFieldVisibilityDecision(
            fieldName: $definition->fieldName,
            accessAllowed: true,
            visible: $profileDecision->visible,
            source: ManageCrudFieldVisibilityDecision::SOURCE_USER_PROFILE,
            reason: $profileDecision->reason,
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
            attributes: [
                'permission' => $definition->permissionKey,
                'fieldType' => $definition->fieldType,
                'sensitive' => $definition->sensitive,
                'hideable' => $definition->hideable,
                'requiredOnForm' => $definition->requiredOnForm,
            ],
        ));
    }

    private function visibilitySourceFor(ManageCrudFieldExternalAccessDecision $decision): string
    {
        return match ($decision->source) {
            ManageCrudFieldExternalAccessDecision::SOURCE_ROLLING => ManageCrudFieldVisibilityDecision::SOURCE_ROLLING,
            default => ManageCrudFieldVisibilityDecision::SOURCE_SYSTEM,
        };
    }
}
