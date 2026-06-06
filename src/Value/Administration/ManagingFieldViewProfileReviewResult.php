<?php

declare(strict_types=1);

namespace App\Managing\Value\Administration;

/**
 * Safe review result for a Managing field view profile edit.
 */
final readonly class ManagingFieldViewProfileReviewResult
{
    /**
     * @param list<string>         $warnings
     * @param array<string, mixed> $normalizedProfilePayload
     * @param array<string, mixed> $reviewContext
     */
    public function __construct(
        public ManagingFieldViewProfileEditRequest $request,
        public string $changeType,
        public string $targetReference,
        public array $normalizedProfilePayload,
        public array $reviewContext,
        public array $warnings = [],
    ) {
    }

    /** @return array<string, mixed> */
    public function toSafeArray(): array
    {
        return [
            'change_type' => $this->changeType,
            'target_reference' => $this->targetReference,
            'review_context' => $this->reviewContext,
            'normalized_profile_payload' => $this->normalizedProfilePayload,
            'warnings' => $this->warnings,
            'safety' => [
                'presentation_only' => true,
                'grants_access' => false,
                'requires_separate_apply' => true,
                'does_not_override_rolling_deny' => true,
            ],
        ];
    }
}
