<?php

declare(strict_types=1);

namespace App\Managing\Value\Administration;

/**
 * Explains one read-only responsibility layer in the Managing field access hierarchy.
 */
final readonly class ManagingFieldAccessMatrixRow
{
    public function __construct(
        public int $priority,
        public string $layer,
        public string $ownerComponent,
        public string $decisionType,
        public string $effect,
        public string $notes,
    ) {
    }
}
