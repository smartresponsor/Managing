<?php

declare(strict_types=1);

namespace App\Managing\Value\Administration;

/**
 * Read-only diagnostic scenario for Managing field visibility explainability screens.
 */
final readonly class ManagingFieldVisibilityExplanationScenario
{
    /** @param list<string> $trace */
    public function __construct(
        public string $scenarioKey,
        public string $label,
        public string $decisionAxis,
        public string $finalDecision,
        public array $trace,
        public string $safetyNote,
    ) {
    }
}
