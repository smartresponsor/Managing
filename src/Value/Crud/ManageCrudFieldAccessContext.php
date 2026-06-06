<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * Authorization context sent from Managing's CRUD runtime toward a field-access decision provider.
 *
 * The subject is intentionally scalar/nullable so this contract can cross component boundaries without
 * coupling Managing to a concrete user entity, EasyAdmin DTO, or security implementation.
 */
final readonly class ManageCrudFieldAccessContext
{
    public const PAGE_INDEX = 'index';
    public const PAGE_DETAIL = 'detail';
    public const PAGE_NEW = 'new';
    public const PAGE_EDIT = 'edit';
    public const PAGE_EXPORT = 'export';

    /** @param array<string, mixed> $attributes */
    public function __construct(
        public string $componentKey,
        public string $resourceClass,
        public string $fieldName,
        public string $pageName,
        public string $operation = 'view',
        public ?string $subjectIdentifier = null,
        public array $attributes = [],
    ) {
    }

    /** @return array<string, mixed> */
    public function toDecisionAttributes(): array
    {
        return [
            'component' => $this->componentKey,
            'resource' => $this->resourceClass,
            'field' => $this->fieldName,
            'page' => $this->pageName,
            'operation' => $this->operation,
            'subject' => $this->subjectIdentifier,
        ] + $this->attributes;
    }
}
