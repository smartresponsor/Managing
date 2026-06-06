<?php

declare(strict_types=1);

namespace App\Managing\Value\Administration;

/**
 * Review-only input for Managing field view profile edits.
 *
 * This value represents presentation preferences only. It must never be used to grant field access.
 */
final readonly class ManagingFieldViewProfileEditRequest
{
    public const SUBJECT_USER = 'user';
    public const SUBJECT_ROLE = 'role';
    public const SUBJECT_GROUP = 'group';

    /**
     * @param list<string> $visibleFields
     * @param list<string> $hiddenFields
     */
    public function __construct(
        public string $subjectType,
        public string $subjectIdentifier,
        public string $pageName,
        public array $visibleFields,
        public array $hiddenFields,
        public ?string $resourceClass = null,
        public ?string $reason = null,
        public string $mode = 'replace',
        public string $requestedBySubject = 'administering:anonymous',
    ) {
    }

    public function subjectKey(): string
    {
        $identifier = trim($this->subjectIdentifier);

        if (str_contains($identifier, ':')) {
            return $identifier;
        }

        return sprintf('%s:%s', $this->subjectType, $identifier);
    }

    public function targetsResource(): bool
    {
        return null !== $this->resourceClass && '' !== trim($this->resourceClass);
    }
}
