<?php

declare(strict_types=1);

namespace App\Managing\Value\Administration;

/**
 * Owner-side control-plane target for field access policy screens and mutation review records.
 */
final readonly class ManagingFieldAccessTarget
{
    /** @param array<string, mixed> $attributes */
    public function __construct(
        public string $componentKey,
        public string $resourceClass,
        public string $fieldName,
        public string $pageName,
        public string $operation = 'view',
        public array $attributes = [],
    ) {
    }

    public function fingerprint(): string
    {
        return implode(':', [
            $this->componentKey,
            str_replace('\\', '.', $this->resourceClass),
            $this->fieldName,
            $this->pageName,
            $this->operation,
        ]);
    }

    /** @return array<string, mixed> */
    public function toAuditContext(): array
    {
        return [
            'component' => $this->componentKey,
            'resource' => $this->resourceClass,
            'field' => $this->fieldName,
            'page' => $this->pageName,
            'operation' => $this->operation,
        ] + $this->attributes;
    }
}
