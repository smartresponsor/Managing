<?php

declare(strict_types=1);

namespace App\Managing\Value;

final readonly class ManageFormDefinition
{
    public function __construct(
        public string $componentKey,
        public string $formKey,
        public string $label,
        public string $formTypeClass,
        public ?string $resourceKey = null,
        public ?string $description = null,
        public ?string $menuGroup = null,
        public bool $enabled = true,
        public string $surface = 'admin',
        public string $mode = 'symfony_form',
    ) {
    }
}
