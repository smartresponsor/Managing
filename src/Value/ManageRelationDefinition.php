<?php

declare(strict_types=1);

namespace App\Managing\Value;

final readonly class ManageRelationDefinition
{
    public function __construct(
        public string $componentKey,
        public string $relationKey,
        public string $label,
        public string $sourceResourceKey,
        public string $targetResourceKey,
        public string $kind = 'association',
        public ?string $sourceField = null,
        public ?string $targetField = null,
        public ?string $description = null,
        public ?string $menuGroup = null,
        public bool $enabled = true,
        public string $surface = 'admin',
    ) {
    }
}
