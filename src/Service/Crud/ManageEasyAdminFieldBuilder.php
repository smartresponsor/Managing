<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use Doctrine\ORM\Mapping as ORM;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Builds concrete EasyAdmin Field instances for Manage CRUD screens.
 *
 * The class owns high-level EasyAdmin field orchestration. Scalar field type
 * construction is delegated to ManageEasyAdminScalarFieldFactory so Doctrine
 * type mapping, enum choice building and explicit type overrides remain
 * independently testable.
 */
final class ManageEasyAdminFieldBuilder
{
    private readonly ManageEasyAdminScalarFieldFactory $scalarFieldFactory;

    public function __construct(
        private readonly ManageEntityReflectionInspector $inspector = new ManageEntityReflectionInspector(),
        private readonly ManageEntityStringifier $stringifier = new ManageEntityStringifier(),
        private readonly ManageCrudFieldPolicy $fieldPolicy = new ManageCrudFieldPolicy(),
        private readonly ManageCrudFieldLabeler $labeler = new ManageCrudFieldLabeler(),
        ?ManageEasyAdminScalarFieldFactory $scalarFieldFactory = null,
        private readonly ManageEasyAdminAssociationFieldFactory $associationFieldFactory = new ManageEasyAdminAssociationFieldFactory(),
        private readonly ManageEasyAdminArrayChoiceFieldFactory $arrayChoiceFieldFactory = new ManageEasyAdminArrayChoiceFieldFactory(),
    ) {
        $this->scalarFieldFactory = $scalarFieldFactory ?? new ManageEasyAdminScalarFieldFactory($this->fieldPolicy);
    }

    public function idField(): object
    {
        return IdField::new('id', 'ID')
            ->formatValue(fn (mixed $value): string => $this->stringifier->stringify($value))
            ->onlyOnIndex();
    }

    public function titleField(string $field): object
    {
        return TextField::new($field, $this->labeler->labelFor($field))->setMaxLength(96);
    }

    public function identityField(string $field): object
    {
        return TextField::new($field, $this->labeler->labelFor($field))->setMaxLength(64);
    }

    public function statusField(string $entityFqcn, string $field): object
    {
        $enumType = $this->inspector->enumTypeFor($entityFqcn, $field);

        if (null !== $enumType) {
            return $this->scalarFieldFactory->enumField($field, $this->labeler->labelFor($field), $enumType);
        }

        return TextField::new($field, $this->labeler->labelFor($field))
            ->setMaxLength(32)
            ->setTemplatePath('admin/field/stringified_text.html.twig')
            ->formatValue(fn (mixed $value): string => $this->stringifier->stringify($value));
    }

    public function publicationFlagField(string $field): object
    {
        return BooleanField::new($field, $this->labeler->labelFor($field));
    }

    public function publicationDateField(string $field): object
    {
        return DateTimeField::new($field, $this->labeler->labelFor($field))->hideOnForm();
    }

    public function descriptionField(): object
    {
        return TextareaField::new('description')->hideOnIndex();
    }

    public function auditDateField(string $field, string $label): object
    {
        return DateTimeField::new($field, $label)->hideOnForm();
    }

    /**
     * @param array<string, array<string, string>>        $arrayChoiceFields
     * @param array<string, array<string, string>|string> $fieldTypeOverrides
     */
    public function formFieldForProperty(\ReflectionProperty $property, array $arrayChoiceFields, array $fieldTypeOverrides): ?object
    {
        if ($field = $this->associationFieldFactory->fieldForProperty($property)) {
            return $field;
        }

        $column = $this->inspector->attributeInstance($property, ORM\Column::class);
        if (null === $column) {
            return null;
        }

        $fieldName = $property->getName();
        $label = $this->labeler->labelFor($fieldName);

        $explicitFieldType = $this->fieldPolicy->explicitFieldType($property->getDeclaringClass()->getName(), $fieldName, $fieldTypeOverrides);
        if (null !== $explicitFieldType) {
            return $this->scalarFieldFactory->explicitField($fieldName, $label, $explicitFieldType);
        }

        if (isset($column->enumType) && is_string($column->enumType) && '' !== $column->enumType) {
            return $this->scalarFieldFactory->enumField($fieldName, $label, $column->enumType);
        }

        $propertyType = $this->inspector->propertyTypeName($property);
        if ('array' === $propertyType) {
            $field = $this->arrayChoiceFieldFactory->fieldForProperty($property, $arrayChoiceFields);
            if (null !== $field) {
                return $field;
            }
        }

        $field = $this->scalarFieldFactory->fieldForPhpType($fieldName, $label, $propertyType);
        if (null !== $field) {
            return $field;
        }

        return $this->scalarFieldFactory->fieldForDoctrineType($fieldName, $label, $column->type ?? 'string', $column->length ?? null);
    }
}
