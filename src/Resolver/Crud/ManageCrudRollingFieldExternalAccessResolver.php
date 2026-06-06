<?php

declare(strict_types=1);

namespace App\Managing\Resolver\Crud;

use App\Managing\ResolverInterface\Crud\ManageCrudFieldExternalAccessResolverInterface;
use App\Managing\Value\Crud\ManageCrudFieldAccessContext;
use App\Managing\Value\Crud\ManageCrudFieldExternalAccessDecision;
use App\Managing\Value\Crud\ManageCrudFieldPermissionVocabulary;

/**
 * Optional Rolling-aware field access adapter.
 *
 * The adapter deliberately avoids Rolling type hints so Managing can still be
 * installed without Rolling. Host applications enable it by selecting the
 * rolling external access backend and wiring Rolling's decision service.
 */
final class ManageCrudRollingFieldExternalAccessResolver implements ManageCrudFieldExternalAccessResolverInterface
{
    private const ROLLING_REQUEST_CLASS = 'App\\Rolling\\Value\\Administration\\RollingFieldAccessDecisionRequest';

    public function __construct(
        private readonly ?object $rollingFieldAccessDecisionService = null,
        private readonly string $permissionKey = ManageCrudFieldPermissionVocabulary::FIELD_VIEW,
        private readonly string $failureEffect = ManageCrudFieldExternalAccessDecision::EFFECT_DENY,
    ) {
    }

    public function decisionFor(ManageCrudFieldAccessContext $context): ManageCrudFieldExternalAccessDecision
    {
        if (null === $this->rollingFieldAccessDecisionService) {
            return $this->failure('rolling_field_access_decision_service_not_configured');
        }

        if (!method_exists($this->rollingFieldAccessDecisionService, 'decide')) {
            return $this->failure('rolling_field_access_decision_method_missing');
        }

        if (!class_exists(self::ROLLING_REQUEST_CLASS)) {
            return $this->failure('rolling_field_access_decision_request_class_missing');
        }

        try {
            $decision = $this->rollingFieldAccessDecisionService->decide($this->buildRollingRequest($context));
        } catch (\Throwable $exception) {
            return $this->failure('rolling_field_access_decision_failed:'.$exception::class);
        }

        if (!is_object($decision)) {
            return $this->failure('rolling_field_access_decision_invalid_response');
        }

        return $this->mapRollingDecision($decision);
    }

    private function buildRollingRequest(ManageCrudFieldAccessContext $context): object
    {
        $requestClass = self::ROLLING_REQUEST_CLASS;

        return new $requestClass(
            permissionKey: $this->permissionKey,
            componentKey: $context->componentKey,
            resourceClass: $context->resourceClass,
            fieldName: $context->fieldName,
            pageName: $context->pageName,
            operation: $context->operation,
            subjectIdentifier: $context->subjectIdentifier,
            attributes: $context->attributes,
        );
    }

    private function mapRollingDecision(object $decision): ManageCrudFieldExternalAccessDecision
    {
        $effect = $this->readEffect($decision);
        $reason = $this->readStringProperty($decision, 'reason');

        return match ($effect) {
            ManageCrudFieldExternalAccessDecision::EFFECT_ALLOW => ManageCrudFieldExternalAccessDecision::allow(
                source: ManageCrudFieldExternalAccessDecision::SOURCE_ROLLING,
                reason: $reason ?? 'rolling_field_access_allowed',
            ),
            ManageCrudFieldExternalAccessDecision::EFFECT_DENY => ManageCrudFieldExternalAccessDecision::deny(
                source: ManageCrudFieldExternalAccessDecision::SOURCE_ROLLING,
                reason: $reason ?? 'rolling_field_access_denied',
            ),
            ManageCrudFieldExternalAccessDecision::EFFECT_ABSTAIN => ManageCrudFieldExternalAccessDecision::abstain(
                source: ManageCrudFieldExternalAccessDecision::SOURCE_ROLLING,
                reason: $reason ?? 'rolling_field_access_abstained',
            ),
            default => $this->failure('rolling_field_access_decision_unknown_effect'),
        };
    }

    private function readEffect(object $decision): ?string
    {
        $effect = $this->readStringProperty($decision, 'effect');
        if (null !== $effect) {
            return $effect;
        }

        if (method_exists($decision, 'allowed') && true === $decision->allowed()) {
            return ManageCrudFieldExternalAccessDecision::EFFECT_ALLOW;
        }

        if (method_exists($decision, 'denied') && true === $decision->denied()) {
            return ManageCrudFieldExternalAccessDecision::EFFECT_DENY;
        }

        return ManageCrudFieldExternalAccessDecision::EFFECT_ABSTAIN;
    }

    private function readStringProperty(object $object, string $property): ?string
    {
        if (!property_exists($object, $property)) {
            return null;
        }

        try {
            $value = $object->{$property};
        } catch (\Throwable) {
            return null;
        }

        return is_string($value) && '' !== $value ? $value : null;
    }

    private function failure(string $reason): ManageCrudFieldExternalAccessDecision
    {
        if (ManageCrudFieldExternalAccessDecision::EFFECT_ABSTAIN === $this->failureEffect) {
            return ManageCrudFieldExternalAccessDecision::abstain(
                source: ManageCrudFieldExternalAccessDecision::SOURCE_ROLLING,
                reason: $reason,
            );
        }

        return ManageCrudFieldExternalAccessDecision::deny(
            source: ManageCrudFieldExternalAccessDecision::SOURCE_ROLLING,
            reason: $reason,
        );
    }
}
