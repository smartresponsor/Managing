<?php

declare(strict_types=1);

namespace App\Managing\Value;

final readonly class ManageProbeResult
{
    public const STATUS_PASSED = 'passed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    /**
     * @param list<string> $messages
     */
    public function __construct(
        public string $componentKey,
        public string $probeKey,
        public string $status,
        public array $messages = [],
    ) {
    }

    public function isPassed(): bool
    {
        return self::STATUS_PASSED === $this->status;
    }
}
