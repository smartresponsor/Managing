<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * Final presentation decision consumed by Managing before EasyAdmin receives fields.
 */
final readonly class ManageCrudFieldVisibilityDecision
{
    public const SOURCE_SYSTEM = 'system';
    public const SOURCE_ROLLING = 'rolling';
    public const SOURCE_ADMINISTERING = 'administering';
    public const SOURCE_USER_PROFILE = 'user_profile';

    public function __construct(
        public string $fieldName,
        public bool $accessAllowed,
        public bool $visible,
        public string $source = self::SOURCE_SYSTEM,
        public ?string $reason = null,
    ) {
    }

    public function renderable(): bool
    {
        return $this->accessAllowed && $this->visible;
    }
}
