<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * Replacement write request for one personal field view profile page rule.
 *
 * The request can only carry presentation preferences. It cannot grant access, deny
 * access, or bypass Rolling/Administering decisions applied before user profiles.
 */
final readonly class ManageCrudFieldUserProfileWriteRequest
{
    /**
     * @param list<string> $visibleFields
     * @param list<string> $hiddenFields
     */
    public function __construct(
        public string $subjectIdentifier,
        public string $pageName,
        public array $visibleFields = [],
        public array $hiddenFields = [],
        public ?string $resourceClass = null,
        public ?string $actorIdentifier = null,
        public ?string $reason = null,
    ) {
    }

    public function targetsResource(): bool
    {
        return null !== $this->resourceClass && '' !== trim($this->resourceClass);
    }

    /** @return list<string> */
    public function normalizedVisibleFields(): array
    {
        return self::stringList($this->visibleFields);
    }

    /** @return list<string> */
    public function normalizedHiddenFields(): array
    {
        return self::stringList($this->hiddenFields);
    }

    public function normalizedSubjectIdentifier(): string
    {
        return trim($this->subjectIdentifier);
    }

    public function normalizedPageName(): string
    {
        return trim($this->pageName);
    }

    public function normalizedResourceClass(): ?string
    {
        if (null === $this->resourceClass) {
            return null;
        }

        $resourceClass = trim($this->resourceClass);

        return '' === $resourceClass ? null : $resourceClass;
    }

    /** @return list<string> */
    private static function stringList(array $values): array
    {
        $normalized = [];
        foreach ($values as $value) {
            if (!is_string($value)) {
                continue;
            }

            $value = trim($value);
            if ('' !== $value && !in_array($value, $normalized, true)) {
                $normalized[] = $value;
            }
        }

        return $normalized;
    }
}
