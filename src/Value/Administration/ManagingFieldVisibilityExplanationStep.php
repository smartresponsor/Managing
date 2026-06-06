<?php

declare(strict_types=1);

namespace App\Managing\Value\Administration;

/**
 * Read-only descriptor for one Managing field visibility explanation stage.
 */
final readonly class ManagingFieldVisibilityExplanationStep
{
    public const AXIS_ACCESS = 'access';
    public const AXIS_PRESENTATION = 'presentation';
    public const AXIS_AVAILABILITY = 'availability';

    public function __construct(
        public int $priority,
        public string $stage,
        public string $ownerComponent,
        public string $decisionAxis,
        public string $decisionEffect,
        public string $terminalBehavior,
        public string $reasonCodeExample,
        public string $notes,
    ) {
    }

    public function accessAxis(): bool
    {
        return self::AXIS_ACCESS === $this->decisionAxis;
    }

    public function presentationAxis(): bool
    {
        return self::AXIS_PRESENTATION === $this->decisionAxis;
    }
}
