<?php

declare(strict_types=1);

namespace App\Managing\Value\Administration;

/**
 * Read-only Managing readiness report for Rolling-backed Managing field access.
 */
final readonly class RollingBackedManagingAccessReadinessReport
{
    /** @param list<RollingBackedManagingAccessReadinessItem> $items */
    public function __construct(
        public string $mode,
        public string $failureEffect,
        public string $permissionKey,
        public string $rollingDecisionContract,
        public string $managingAdapterContract,
        public array $items,
    ) {
    }

    /** @return array<string, mixed> */
    public function toSafeArray(): array
    {
        return [
            'mode' => $this->mode,
            'failure_effect' => $this->failureEffect,
            'permission_key' => $this->permissionKey,
            'rolling_decision_contract' => $this->rollingDecisionContract,
            'managing_adapter_contract' => $this->managingAdapterContract,
            'items' => array_map(
                static fn (RollingBackedManagingAccessReadinessItem $item): array => $item->toSafeArray(),
                $this->items,
            ),
            'safety' => [
                'read_only' => true,
                'grants_access' => false,
                'mutates_rolling_acl' => false,
                'mutates_managing_runtime' => false,
                'frontend_only_visibility' => false,
            ],
        ];
    }
}
