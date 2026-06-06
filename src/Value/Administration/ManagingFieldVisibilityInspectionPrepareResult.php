<?php

declare(strict_types=1);

namespace App\Managing\Value\Administration;

/**
 * Safe Administering preparation result for a Managing field visibility inspection call.
 */
final readonly class ManagingFieldVisibilityInspectionPrepareResult
{
    /**
     * @param array<string, mixed> $managingInspectionPayload
     * @param array<string, mixed> $inspectionContext
     * @param list<string>         $warnings
     */
    private function __construct(
        public bool $accepted,
        public string $reason,
        public array $managingInspectionPayload = [],
        public array $inspectionContext = [],
        public array $warnings = [],
    ) {
    }

    /**
     * @param array<string, mixed> $managingInspectionPayload
     * @param array<string, mixed> $inspectionContext
     * @param list<string>         $warnings
     */
    public static function accepted(array $managingInspectionPayload, array $inspectionContext, array $warnings = []): self
    {
        return new self(true, 'field_visibility_inspection_payload_prepared', $managingInspectionPayload, $inspectionContext, $warnings);
    }

    /** @param list<string> $warnings */
    public static function rejected(string $reason, array $warnings = []): self
    {
        return new self(false, $reason, [], [], $warnings);
    }

    /** @return array<string, mixed> */
    public function toSafeArray(): array
    {
        return [
            'accepted' => $this->accepted,
            'reason' => $this->reason,
            'managing_inspection_contract' => 'App\\Managing\\InspectorInterface\\Crud\\ManageCrudFieldVisibilityInspectorInterface',
            'managing_command' => 'managing:field-visibility:explain',
            'managing_inspection_payload' => $this->managingInspectionPayload,
            'inspection_context' => $this->inspectionContext,
            'warnings' => $this->warnings,
            'safety' => [
                'read_only' => true,
                'grants_access' => false,
                'renders_field_values' => false,
                'requires_managing_runtime' => true,
                'does_not_override_rolling_deny' => true,
            ],
        ];
    }
}
