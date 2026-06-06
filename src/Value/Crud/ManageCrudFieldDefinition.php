<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * Neutral field metadata discovered by Managing before EasyAdmin field objects are created.
 *
 * This value is the seam that lets security, administrative policy, and user presentation preferences be
 * applied before sensitive or disallowed data reaches the rendered admin surface.
 */
final readonly class ManageCrudFieldDefinition
{
    /**
     * @param list<string>         $availableOn
     * @param array<string, mixed> $options
     */
    public function __construct(
        public string $componentKey,
        public string $resourceClass,
        public string $fieldName,
        public string $label,
        public string $fieldType = 'text',
        public array $availableOn = ['index', 'detail', 'new', 'edit'],
        public bool $hideable = true,
        public bool $sensitive = false,
        public bool $requiredOnForm = false,
        public bool $defaultVisible = true,
        public ?string $permissionKey = ManageCrudFieldPermissionVocabulary::FIELD_VIEW,
        public array $options = [],
    ) {
    }

    public function isAvailableOn(string $pageName): bool
    {
        return in_array($pageName, $this->availableOn, true);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'componentKey' => $this->componentKey,
            'resourceClass' => $this->resourceClass,
            'fieldName' => $this->fieldName,
            'label' => $this->label,
            'fieldType' => $this->fieldType,
            'availableOn' => $this->availableOn,
            'hideable' => $this->hideable,
            'sensitive' => $this->sensitive,
            'requiredOnForm' => $this->requiredOnForm,
            'defaultVisible' => $this->defaultVisible,
            'permissionKey' => $this->permissionKey,
            'options' => $this->options,
        ];
    }
}
