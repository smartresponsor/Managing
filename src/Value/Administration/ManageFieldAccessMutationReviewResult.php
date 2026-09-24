<?php

declare(strict_types=1);

namespace App\Managing\Value\Administration;

/**
 * Review result returned to Managing field access control-plane screens.
 */
final readonly class ManageFieldAccessMutationReviewResult
{
    /** @param array<string, mixed> $review */
    public function __construct(
        public ManageFieldAccessPolicyDescriptor $descriptor,
        public array $review,
        public ?string $requestKey = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toSafeArray(): array
    {
        return [
            'descriptor' => [
                'permission' => $this->descriptor->permissionKey,
                'subject_type' => $this->descriptor->subjectType,
                'subject_identifier' => $this->descriptor->subjectIdentifier,
                'effect' => $this->descriptor->effect,
                'target' => $this->descriptor->target->toAuditContext(),
            ],
            'request_key' => $this->requestKey,
            'review' => $this->review,
        ];
    }

    public function hasRequestKey(): bool
    {
        return null !== $this->requestKey && '' !== $this->requestKey;
    }
}
