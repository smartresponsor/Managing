<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * Result returned by the controlled Managing field view profile apply handler.
 */
final readonly class ManageCrudFieldUserProfileApplyResult
{
    /**
     * @param array<string, mixed> $normalizedProfileConfig
     * @param list<string>         $warnings
     */
    private function __construct(
        public bool $accepted,
        public string $reason,
        public array $normalizedProfileConfig = [],
        public array $warnings = [],
    ) {
    }

    /**
     * @param array<string, mixed> $normalizedProfileConfig
     * @param list<string>         $warnings
     */
    public static function accepted(array $normalizedProfileConfig, string $reason = 'field_user_profile_apply_prepared', array $warnings = []): self
    {
        return new self(true, $reason, $normalizedProfileConfig, $warnings);
    }

    /** @param list<string> $warnings */
    public static function rejected(string $reason, array $warnings = []): self
    {
        return new self(false, $reason, [], $warnings);
    }
}
