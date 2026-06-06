<?php

declare(strict_types=1);

namespace App\Managing\Factory\Crud;

use App\Managing\Labeler\Crud\ManageCrudFieldLabeler;
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

        return $this->fieldForChoices($fieldName, $this->labeler->labelFor($fieldName), $arrayChoiceFields[$fieldName] ?? []);
    }

    /** @param array<string, string> $choices */
    public function fieldForChoices(string $fieldName, string $label, array $choices): ?object
    {
        if ([] === $choices) {
            return null;
        }

        return ChoiceField::new($fieldName, $label)
            ->setChoices($choices)
            ->allowMultipleChoices()
            ->renderExpanded(false)
            ->autocomplete();
    }
}
