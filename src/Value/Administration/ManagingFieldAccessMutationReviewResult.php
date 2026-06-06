<?php

declare(strict_types=1);

namespace App\Managing\Value\Administration;

use App\Rolling\Value\Administration\RollingAclMutationReview;

/**
 * Review result returned to Managing field access control-plane screens.
 */
final readonly class ManagingFieldAccessMutationReviewResult
{
    public function __construct(
        public ManagingFieldAccessPolicyDescriptor $descriptor,
        public RollingAclMutationReview $review,
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
            'review' => $this->review->toSafeArray(),
        ];
    }

    public function hasRequestKey(): bool
    {
        return null !== $this->requestKey && '' !== $this->requestKey;
    }
}
