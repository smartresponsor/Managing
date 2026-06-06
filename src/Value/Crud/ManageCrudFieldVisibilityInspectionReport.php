<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * Result of a concrete Managing field visibility inspection request.
 */
final readonly class ManageCrudFieldVisibilityInspectionReport
{
    public function __construct(
        public ManageCrudFieldVisibilityInspectionRequest $request,
        public ?ManageCrudFieldVisibilityExplanation $explanation = null,
        public ?string $reason = null,
    ) {
    }

    public function found(): bool
    {
        return null !== $this->explanation;
    }

    public function renderable(): bool
    {
        return null !== $this->explanation && $this->explanation->renderable();
    }

    public function decisionAxis(): string
    {
        if (null === $this->explanation) {
            return 'not_found';
        }

        return $this->explanation->finalAxis();
    }

    public function statusLabel(): string
    {
        if (null === $this->explanation) {
            return 'not_found';
        }

        if ($this->explanation->denied()) {
            return 'denied';
        }

        if ($this->explanation->hidden()) {
            return 'hidden';
        }

        return 'visible';
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'request' => $this->request->toArray(),
            'found' => $this->found(),
            'status' => $this->statusLabel(),
            'decisionAxis' => $this->decisionAxis(),
            'reason' => $this->reason,
            'explanation' => $this->explanation?->toArray(),
        ];
    }
}
