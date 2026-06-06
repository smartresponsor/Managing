<?php

declare(strict_types=1);

namespace App\Managing\Value\Administration;

/**
 * Apply-preparation request for a reviewed Managing field view profile payload.
 */
final readonly class ManagingFieldViewProfileApplyRequest
{
    /**
     * @param array<string, mixed> $normalizedProfilePayload
     * @param array<string, mixed> $reviewContext
     */
    public function __construct(
        public array $normalizedProfilePayload,
        public array $reviewContext,
        public string $requestedBySubject = 'administering:anonymous',
        public ?string $reason = null,
    ) {
    }
}
