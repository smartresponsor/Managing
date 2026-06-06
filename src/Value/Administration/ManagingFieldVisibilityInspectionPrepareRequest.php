<?php

declare(strict_types=1);

namespace App\Managing\Value\Administration;

/**
 * Administering-side preparation input for a concrete Managing visibility inspection request.
 *
 * This value is diagnostic-only. It must never be used to grant access or to render field values.
 */
final readonly class ManagingFieldVisibilityInspectionPrepareRequest
{
    /**
     * @param list<string> $statusCandidates
     * @param list<string> $publicationFlagCandidates
     * @param list<string> $publicationDateCandidates
     */
    public function __construct(
        public string $resourceClass,
        public string $fieldName,
        public string $pageName,
        public ?string $subjectIdentifier = null,
        public array $statusCandidates = [],
        public array $publicationFlagCandidates = [],
        public array $publicationDateCandidates = [],
        public string $requestedBySubject = 'administering:anonymous',
        public ?string $reason = null,
    ) {
    }

    /** @return array<string, mixed> */
    public function toManagingInspectionPayload(): array
    {
        return [
            'resourceClass' => trim($this->resourceClass),
            'fieldName' => trim($this->fieldName),
            'pageName' => strtolower(trim($this->pageName)),
            'subjectIdentifier' => $this->subjectIdentifier,
            'statusCandidates' => $this->statusCandidates,
            'publicationFlagCandidates' => $this->publicationFlagCandidates,
            'publicationDateCandidates' => $this->publicationDateCandidates,
        ];
    }
}
