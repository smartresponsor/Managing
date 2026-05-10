<?php

declare(strict_types=1);

namespace App\Managing\Value;

final readonly class ManageProbeDefinition
{
    public function __construct(
        public string $componentKey,
        public string $probeKey,
        public string $label,
        public ?string $description = null,
        public bool $enabled = true,
    ) {
    }
}
