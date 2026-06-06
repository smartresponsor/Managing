<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * Result returned by a Managing field view profile reader backend.
 */
final readonly class ManageCrudFieldUserProfileReadResult
{
    /** @param array<string, mixed> $normalizedProfileConfig */
    private function __construct(
        public bool $available,
        public string $reason,
        public array $normalizedProfileConfig = [],
    ) {
    }

    /** @param array<string, mixed> $normalizedProfileConfig */
    public static function available(array $normalizedProfileConfig, string $reason = 'field_user_profile_read_available'): self
    {
        return new self(true, $reason, ManageCrudFieldUserProfileRuleSet::fromArray($normalizedProfileConfig)->toArray());
    }

    public static function unavailable(string $reason): self
    {
        return new self(false, $reason);
    }
}
