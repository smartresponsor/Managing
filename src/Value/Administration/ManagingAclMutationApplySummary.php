<?php

declare(strict_types=1);

namespace App\Managing\Value\Administration;

/**
 * Metadata-only summary of ACL apply records stored by Administering.
 *
 * This summary is safe for UI/reporting and must not contain raw policy internals,
 * secrets, sessions, credentials, or account security fields.
 */
final readonly class ManagingAclMutationApplySummary
{
    /**
     * @param array<string, int> $countByStatus
     * @param array<string, int> $countByMutationType
     */
    public function __construct(
        private int $total,
        private int $succeeded,
        private int $failed,
        private array $countByStatus,
        private array $countByMutationType,
        private ?\DateTimeImmutable $latestAt,
    ) {
    }

    public function total(): int
    {
        return $this->total;
    }

    public function succeeded(): int
    {
        return $this->succeeded;
    }

    public function failed(): int
    {
        return $this->failed;
    }

    /** @return array<string, int> */
    public function countByStatus(): array
    {
        return $this->countByStatus;
    }

    /** @return array<string, int> */
    public function countByMutationType(): array
    {
        return $this->countByMutationType;
    }

    public function latestAt(): ?\DateTimeImmutable
    {
        return $this->latestAt;
    }

    /** @return array<string, mixed> */
    public function toSafeArray(): array
    {
        return [
            'total' => $this->total,
            'succeeded' => $this->succeeded,
            'failed' => $this->failed,
            'count_by_status' => $this->countByStatus,
            'count_by_mutation_type' => $this->countByMutationType,
            'latest_at' => $this->latestAt?->format(DATE_ATOM),
        ];
    }
}
