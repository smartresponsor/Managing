<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * Diagnostic input for inspecting one Managing CRUD field visibility decision.
 */
final readonly class ManageCrudFieldVisibilityInspectionRequest
{
    /**
     * @param list<string>                                $statusCandidates
     * @param list<string>                                $publicationFlagCandidates
     * @param list<string>                                $publicationDateCandidates
     * @param array<string, array<string, string>>        $arrayChoiceFields
     * @param array<string, array<string, string>|string> $fieldTypeOverrides
     */
    public function __construct(
        public string $resourceClass,
        public string $fieldName,
        public string $pageName,
        public ?string $subjectIdentifier = null,
        public array $statusCandidates = [],
        public array $publicationFlagCandidates = [],
        public array $publicationDateCandidates = [],
        public array $arrayChoiceFields = [],
        public array $fieldTypeOverrides = [],
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'resourceClass' => $this->resourceClass,
            'fieldName' => $this->fieldName,
            'pageName' => $this->pageName,
            'subjectIdentifier' => $this->subjectIdentifier,
            'statusCandidates' => $this->statusCandidates,
            'publicationFlagCandidates' => $this->publicationFlagCandidates,
            'publicationDateCandidates' => $this->publicationDateCandidates,
            'arrayChoiceFields' => $this->arrayChoiceFields,
            'fieldTypeOverrides' => $this->fieldTypeOverrides,
        ];
    }
}
