<?php

declare(strict_types=1);

namespace App\Managing\Value\Administration;

/**
 * One read-only readiness/checklist item for Rolling-backed Managing field access activation.
 */
final readonly class RollingBackedManagingAccessReadinessItem
{
    public function __construct(
        public string $key,
        public string $label,
        public string $status,
        public string $owner,
        public string $expectedValue,
        public string $note,
    ) {
    }

    /** @return array<string, string> */
    public function toSafeArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'status' => $this->status,
            'owner' => $this->owner,
            'expected_value' => $this->expectedValue,
            'note' => $this->note,
        ];
    }
}
