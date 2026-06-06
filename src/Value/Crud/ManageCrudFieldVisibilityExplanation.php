<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * Explainable diagnostic result for a field before EasyAdmin receives it.
 */
final readonly class ManageCrudFieldVisibilityExplanation
{
    /** @param list<ManageCrudFieldVisibilityExplanationStep> $steps */
    public function __construct(
        public ManageCrudFieldDefinition $definition,
        public string $pageName,
        public ?string $subjectIdentifier,
        public ManageCrudFieldVisibilityDecision $finalDecision,
        public array $steps = [],
    ) {
    }

    public function renderable(): bool
    {
        return $this->finalDecision->renderable();
    }

    public function denied(): bool
    {
        return !$this->finalDecision->accessAllowed;
    }

    public function hidden(): bool
    {
        return $this->finalDecision->accessAllowed && !$this->finalDecision->visible;
    }

    public function finalAxis(): string
    {
        foreach ($this->steps as $step) {
            if ($step->terminal) {
                return $step->axis;
            }
        }

        return $this->denied()
            ? ManageCrudFieldVisibilityExplanationStep::AXIS_ACCESS
            : ManageCrudFieldVisibilityExplanationStep::AXIS_PRESENTATION;
    }

    public function accessDenied(): bool
    {
        return $this->denied() && ManageCrudFieldVisibilityExplanationStep::AXIS_ACCESS === $this->finalAxis();
    }

    public function presentationHidden(): bool
    {
        return $this->hidden();
    }

    /** @return list<string> */
    public function reasons(): array
    {
        return array_values(array_unique(array_map(
            static fn (ManageCrudFieldVisibilityExplanationStep $step): string => $step->reason,
            $this->steps,
        )));
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'fieldName' => $this->definition->fieldName,
            'resourceClass' => $this->definition->resourceClass,
            'pageName' => $this->pageName,
            'subjectIdentifier' => $this->subjectIdentifier,
            'renderable' => $this->renderable(),
            'accessAllowed' => $this->finalDecision->accessAllowed,
            'visible' => $this->finalDecision->visible,
            'finalAxis' => $this->finalAxis(),
            'accessDenied' => $this->accessDenied(),
            'presentationHidden' => $this->presentationHidden(),
            'source' => $this->finalDecision->source,
            'reason' => $this->finalDecision->reason,
            'definition' => $this->definition->toArray(),
            'steps' => array_map(
                static fn (ManageCrudFieldVisibilityExplanationStep $step): array => $step->toArray(),
                $this->steps,
            ),
        ];
    }
}
