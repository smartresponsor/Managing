<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * Result returned by a Managing personal field view profile writer seam.
 *
 * The normalized profile payload is intentionally config-shaped so a later SQLite/system
 * storage implementation or Administering UI can persist the same contract.
 */
final readonly class ManageCrudFieldUserProfileWriteResult
{
    /** @param array<string, mixed> $normalizedProfileConfig */
    private function __construct(
        public bool $accepted,
        public string $reason,
        public array $normalizedProfileConfig = [],
    ) {
    }

    /** @param array<string, mixed> $normalizedProfileConfig */
    public static function accepted(array $normalizedProfileConfig, string $reason = 'field_user_profile_write_payload_prepared'): self
    {
        return new self(true, $reason, $normalizedProfileConfig);
    }

    public static function rejected(string $reason): self
    {
        return new self(false, $reason);
    }
}
