<?php

declare(strict_types=1);

namespace App\Managing\Value;

final readonly class ManageRouteDefinition
{
    public function __construct(
        public string $componentKey,
        public string $routeName,
        public string $label,
        public string $kind = 'individual',
        public ?string $menuGroup = null,
        public bool $enabled = true,
    ) {
    }
}
