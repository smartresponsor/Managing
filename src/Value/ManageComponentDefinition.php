<?php

declare(strict_types=1);

namespace App\Managing\Value;

final readonly class ManageComponentDefinition
{
    public function __construct(
        public string $key,
        public string $label,
        public ?string $description = null,
    ) {
    }
}
