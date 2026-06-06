<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * One explainability step from the Managing field visibility pipeline.
 */
final readonly class ManageCrudFieldVisibilityExplanationStep
{
    public const EFFECT_ALLOW = 'allow';
    public const EFFECT_DENY = 'deny';
    public const EFFECT_VISIBLE = 'visible';
    public const EFFECT_HIDDEN = 'hidden';
    public const EFFECT_ABSTAIN = 'abstain';
    public const EFFECT_IGNORED = 'ignored';

    public const AXIS_ACCESS = 'access';
    public const AXIS_PRESENTATION = 'presentation';
    public const AXIS_AVAILABILITY = 'availability';

    /** @param array<string, mixed> $context */
    public function __construct(
        public string $source,
        public string $effect,
        public string $reason,
        public bool $terminal = false,
        public array $context = [],
        public string $axis = self::AXIS_PRESENTATION,
    ) {
    }

    public function accessAxis(): bool
    {
        return self::AXIS_ACCESS === $this->axis;
    }

    public function presentationAxis(): bool
    {
        return self::AXIS_PRESENTATION === $this->axis;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'source' => $this->source,
            'effect' => $this->effect,
            'reason' => $this->reason,
            'terminal' => $this->terminal,
            'axis' => $this->axis,
            'context' => $this->context,
        ];
    }
}
