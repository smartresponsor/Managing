<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * Controlled apply request for a reviewed Managing field view profile payload.
 *
 * The request carries presentation-only profile data. It must not grant access and must
 * originate from a reviewed Administering payload or an equivalent trusted control plane.
 */
final readonly class ManageCrudFieldUserProfileApplyRequest
{
    /**
     * @param array<string, mixed> $normalizedProfilePayload
     * @param array<string, mixed> $reviewContext
     */
    public function __construct(
        public array $normalizedProfilePayload,
        public array $reviewContext,
        public string $actorIdentifier = 'managing:system',
        public ?string $reason = null,
    ) {
    }
}
