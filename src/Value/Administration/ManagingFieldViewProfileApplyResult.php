<?php

declare(strict_types=1);

namespace App\Managing\Value\Administration;

/**
 * Safe result for a Managing field view profile apply-preparation step.
 */
final readonly class ManagingFieldViewProfileApplyResult
{
    /**
     * @param array<string, mixed> $managingApplyPayload
     * @param array<string, mixed> $reviewContext
     * @param list<string>         $warnings
     */
    private function __construct(
        public bool $accepted,
        public string $reason,
        public array $managingApplyPayload = [],
        public array $reviewContext = [],
        public array $warnings = [],
    ) {
    }

    /**
     * @param array<string, mixed> $managingApplyPayload
     * @param array<string, mixed> $reviewContext
     * @param list<string>         $warnings
     */
    public static function accepted(array $managingApplyPayload, array $reviewContext, array $warnings = []): self
    {
        return new self(true, 'field_view_profile_apply_payload_prepared', $managingApplyPayload, $reviewContext, $warnings);
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
            'managing_apply_contract' => 'App\\Managing\\HandlerInterface\\Crud\\ManageCrudFieldUserProfileApplyHandlerInterface',
            'managing_apply_payload' => $this->managingApplyPayload,
            'review_context' => $this->reviewContext,
            'warnings' => $this->warnings,
            'safety' => [
                'presentation_only' => true,
                'grants_access' => false,
                'requires_managing_writer_backend' => true,
                'does_not_override_rolling_deny' => true,
            ],
        ];
    }
}
