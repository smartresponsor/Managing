<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * Result of a host-facing profile storage activation check.
 */
final readonly class ManageCrudFieldUserProfileStorageActivationReport
{
    /** @param list<ManageCrudFieldUserProfileStorageActivationIssue> $issues */
    public function __construct(
        public bool $doctrineStorageEnabled,
        public bool $passed,
        public array $issues,
    ) {
    }

    /** @param list<ManageCrudFieldUserProfileStorageActivationIssue> $issues */
    public static function fromIssues(bool $doctrineStorageEnabled, array $issues): self
    {
        $hasErrors = false;
        foreach ($issues as $issue) {
            if ($issue->isError()) {
                $hasErrors = true;
                break;
            }
        }

        return new self($doctrineStorageEnabled, !$hasErrors, $issues);
    }

    public function statusLabel(): string
    {
        return $this->passed ? 'pass' : 'fail';
    }
}
