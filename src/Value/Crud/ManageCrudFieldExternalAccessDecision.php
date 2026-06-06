<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * Security/access decision returned by an optional external field-access provider.
 *
 * The value is intentionally independent from Rolling classes so Managing can run
 * without Rolling while still exposing a stable integration seam for host apps.
 */
final readonly class ManageCrudFieldExternalAccessDecision
{
    public const EFFECT_ALLOW = 'allow';
    public const EFFECT_DENY = 'deny';
    public const EFFECT_ABSTAIN = 'abstain';

    public const SOURCE_LOCAL = 'local';
    public const SOURCE_ROLLING = 'rolling';
    public const SOURCE_EXTERNAL = 'external';

    public function __construct(
        public string $effect,
        public string $source = self::SOURCE_LOCAL,
        public ?string $reason = null,
    ) {
    }

    public static function allow(string $source = self::SOURCE_EXTERNAL, ?string $reason = null): self
    {
        return new self(self::EFFECT_ALLOW, $source, $reason);
    }

    public static function deny(string $source = self::SOURCE_EXTERNAL, ?string $reason = null): self
    {
        return new self(self::EFFECT_DENY, $source, $reason);
    }

    public static function abstain(string $source = self::SOURCE_LOCAL, ?string $reason = null): self
    {
        return new self(self::EFFECT_ABSTAIN, $source, $reason);
    }

    public function allows(): bool
    {
        return self::EFFECT_ALLOW === $this->effect;
    }

    public function denies(): bool
    {
        return self::EFFECT_DENY === $this->effect;
    }

    public function abstains(): bool
    {
        return self::EFFECT_ABSTAIN === $this->effect;
    }
}
