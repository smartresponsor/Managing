<?php

declare(strict_types=1);

namespace App\Managing\Factory\Crud;

use App\Managing\Value\Crud\ManageCrudFieldExternalAccessDecision;
use App\Managing\Value\Crud\ManageCrudFieldVisibilityDecision;
use App\Managing\Value\Crud\ManageCrudFieldVisibilityExplanationStep;

/**
 * Creates normalized diagnostic steps for field visibility explanations.
 */
final class ManageCrudFieldVisibilityExplanationStepFactory
{
    public function fromVisibilityDecision(ManageCrudFieldVisibilityDecision $decision, bool $terminal = false): ManageCrudFieldVisibilityExplanationStep
    {
        $effect = !$decision->accessAllowed
            ? ManageCrudFieldVisibilityExplanationStep::EFFECT_DENY
            : ($decision->visible ? ManageCrudFieldVisibilityExplanationStep::EFFECT_VISIBLE : ManageCrudFieldVisibilityExplanationStep::EFFECT_HIDDEN);

        $axis = !$decision->accessAllowed
            ? ManageCrudFieldVisibilityExplanationStep::AXIS_ACCESS
            : ManageCrudFieldVisibilityExplanationStep::AXIS_PRESENTATION;

        return $this->create($decision->source, $effect, $decision->reason ?? 'field_visibility_decision', $terminal, axis: $axis);
    }

    public function fromExternalDecision(ManageCrudFieldExternalAccessDecision $decision, ?string $permissionKey = null): ManageCrudFieldVisibilityExplanationStep
    {
        $effect = match ($decision->effect) {
            ManageCrudFieldExternalAccessDecision::EFFECT_ALLOW => ManageCrudFieldVisibilityExplanationStep::EFFECT_ALLOW,
            ManageCrudFieldExternalAccessDecision::EFFECT_DENY => ManageCrudFieldVisibilityExplanationStep::EFFECT_DENY,
            default => ManageCrudFieldVisibilityExplanationStep::EFFECT_ABSTAIN,
        };

        $context = [];
        if (null !== $permissionKey && '' !== $permissionKey) {
            $context['permission'] = $permissionKey;
        }

        $reason = $this->externalReason($decision);
        if (null !== $decision->reason && $reason !== $decision->reason) {
            $context['providerReason'] = $decision->reason;
        }

        return $this->create(
            $decision->source,
            $effect,
            $reason,
            $decision->denies(),
            $context,
            ManageCrudFieldVisibilityExplanationStep::AXIS_ACCESS,
        );
    }

    /** @param array<string, mixed> $context */
    public function create(
        string $source,
        string $effect,
        string $reason,
        bool $terminal = false,
        array $context = [],
        string $axis = ManageCrudFieldVisibilityExplanationStep::AXIS_PRESENTATION,
    ): ManageCrudFieldVisibilityExplanationStep {
        return new ManageCrudFieldVisibilityExplanationStep($source, $effect, $reason, $terminal, $context, $axis);
    }

    private function externalReason(ManageCrudFieldExternalAccessDecision $decision): string
    {
        if ($decision->denies() && ManageCrudFieldExternalAccessDecision::SOURCE_ROLLING === $decision->source) {
            return 'rolling_field_value_access_denied';
        }

        if ($decision->allows() && ManageCrudFieldExternalAccessDecision::SOURCE_ROLLING === $decision->source) {
            return 'rolling_field_value_access_allowed';
        }

        return $decision->reason ?? 'external_field_access_decision';
    }
}
