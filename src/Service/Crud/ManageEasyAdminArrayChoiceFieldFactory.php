<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

/**
 * Builds EasyAdmin ChoiceField instances for array-backed form fields.
 */
final class ManageEasyAdminArrayChoiceFieldFactory
{
    public function __construct(
        private readonly ManageCrudFieldLabeler $labeler = new ManageCrudFieldLabeler(),
    ) {
    }

    /** @param array<string, array<string, string>> $arrayChoiceFields */
    public function fieldForProperty(\ReflectionProperty $property, array $arrayChoiceFields): ?object
    {
        $fieldName = $property->getName();
        $choices = $arrayChoiceFields[$fieldName] ?? [];

        if ([] === $choices) {
            return null;
        }

        return ChoiceField::new($fieldName, $this->labeler->labelFor($fieldName))
            ->setChoices($choices)
            ->allowMultipleChoices()
            ->renderExpanded(false)
            ->autocomplete();
    }
}
