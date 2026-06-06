<?php

declare(strict_types=1);

namespace App\Managing\Value\Administration;

/**
 * Read-only control-plane row for Managing field access permissions.
 */
final readonly class ManagingFieldAccessCatalogItem
{
    /** @param list<string> $scopes */
    public function __construct(
        public string $permissionKey,
        public string $label,
        public string $category,
        public string $controlPlaneGroup,
        public array $scopes,
        public bool $sensitive,
        public bool $registeredInRolling,
    ) {
    }

    public function missingRegistration(): bool
    {
        return !$this->registeredInRolling;
    }
}
