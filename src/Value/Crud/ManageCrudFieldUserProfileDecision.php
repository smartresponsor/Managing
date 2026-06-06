<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * Personal presentation preference resolved for a field after access policy has allowed it.
 *
 * This value is intentionally unable to deny field access. It can only request visible/hidden
 * presentation inside the corridor already opened by system, Rolling, and admin policy.
 */
final readonly class ManageCrudFieldUserProfileDecision
{
    private function __construct(
        public string $fieldName,
        public bool $visible,
        public string $source = ManageCrudFieldVisibilityDecision::SOURCE_USER_PROFILE,
        public ?string $reason = null,
    ) {
    }

    public static function visible(string $fieldName, ?string $reason = 'user_profile_visible'): self
    {
        return new self($fieldName, true, reason: $reason);
    }

    public static function hidden(string $fieldName, ?string $reason = 'user_profile_hidden'): self
    {
        return new self($fieldName, false, reason: $reason);
    }
}
