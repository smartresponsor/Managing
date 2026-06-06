<?php

declare(strict_types=1);

namespace App\Managing\Factory\Crud;

use App\Managing\Builder\Crud\ManageEasyAdminFieldBuilder;
use App\Managing\FactoryInterface\Crud\ManageEasyAdminFieldFromDefinitionFactoryInterface;
use App\Managing\Value\Crud\ManageCrudFieldDefinition;

/**
 * Converts neutral Managing field definitions into EasyAdmin Field objects.
 */
final class ManageEasyAdminFieldFromDefinitionFactory implements ManageEasyAdminFieldFromDefinitionFactoryInterface
{
    public function __construct(
        private readonly ManageEasyAdminFieldBuilder $fieldBuilder = new ManageEasyAdminFieldBuilder(),
        private readonly ManageEasyAdminScalarFieldFactory $scalarFieldFactory = new ManageEasyAdminScalarFieldFactory(),
        private readonly ManageEasyAdminAssociationFieldFactory $associationFieldFactory = new ManageEasyAdminAssociationFieldFactory(),
        private readonly ManageEasyAdminArrayChoiceFieldFactory $arrayChoiceFieldFactory = new ManageEasyAdminArrayChoiceFieldFactory(),
    ) {
    }

    public function fieldForDefinition(ManageCrudFieldDefinition $definition): ?object
    {
        return match ($definition->fieldType) {
            'identifier' => $this->fieldBuilder->idField($definition->fieldName),
            'title' => $this->fieldBuilder->titleField($definition->fieldName),
            'identity' => $this->fieldBuilder->identityField($definition->fieldName),
            'status' => $this->fieldBuilder->statusField($definition->resourceClass, $definition->fieldName),
            'publication_flag' => $this->fieldBuilder->publicationFlagField($definition->fieldName),
            'publication_date' => $this->fieldBuilder->publicationDateField($definition->fieldName),
            'description' => $this->fieldBuilder->descriptionField($definition->fieldName),
            'audit_date' => $this->fieldBuilder->auditDateField($definition->fieldName),
            'association' => $this->associationFieldFactory->fieldForName($definition->fieldName, $definition->label),
            'array_choice' => $this->arrayChoiceFieldFactory->fieldForChoices(
                $definition->fieldName,
                $definition->label,
                $this->choices($definition),
            ),
            'enum' => $this->enumField($definition),
            default => $this->scalarFieldFactory->explicitField(
                $definition->fieldName,
                $definition->label,
                $this->normalizedScalarType($definition->fieldType),
            ),
        };
    }

    private function enumField(ManageCrudFieldDefinition $definition): ?object
    {
        $enumType = $definition->options['enumType'] ?? null;
        if (!is_string($enumType) || '' === $enumType) {
            return null;
        }

        return $this->scalarFieldFactory->enumField($definition->fieldName, $definition->label, $enumType);
    }

    /** @return array<string, string> */
    private function choices(ManageCrudFieldDefinition $definition): array
    {
        $choices = $definition->options['choices'] ?? [];

        return is_array($choices) ? $choices : [];
    }

    private function normalizedScalarType(string $fieldType): string
    {
        return match ($fieldType) {
            'bool' => 'boolean',
            'int' => 'integer',
            'float' => 'number',
            'string' => 'text',
            default => $fieldType,
        };
    }
}
