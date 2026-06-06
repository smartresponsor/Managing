<?php

declare(strict_types=1);

namespace App\Managing\Resolver\Crud;

use App\Managing\Inspector\Crud\ManageEntityReflectionInspector;
use App\Managing\Policy\Crud\ManageCrudFieldPolicy;
use App\Managing\ResolverInterface\Crud\ManageCrudFieldDefinitionTypeResolverInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Resolves neutral field-definition types from reflection metadata.
 */
final class ManageCrudFieldDefinitionTypeResolver implements ManageCrudFieldDefinitionTypeResolverInterface
{
    public function __construct(
        private readonly ManageEntityReflectionInspector $inspector = new ManageEntityReflectionInspector(),
        private readonly ManageCrudFieldPolicy $fieldPolicy = new ManageCrudFieldPolicy(),
    ) {
    }

    /**
     * @param array<string, array<string, string>>        $arrayChoiceFields
     * @param array<string, array<string, string>|string> $fieldTypeOverrides
     *
     * @return array{0: string, 1: array<string, mixed>}|null
     */
    public function typeForProperty(
        string $entityFqcn,
        \ReflectionProperty $property,
        array $arrayChoiceFields = [],
        array $fieldTypeOverrides = [],
    ): ?array {
        $fieldName = $property->getName();
        $explicitType = $this->fieldPolicy->explicitFieldType($entityFqcn, $fieldName, $fieldTypeOverrides);
        if (null !== $explicitType) {
            return [$explicitType, []];
        }

        foreach ([ORM\ManyToOne::class, ORM\OneToOne::class] as $relationAttribute) {
            if (null !== $this->inspector->attributeInstance($property, $relationAttribute)) {
                return ['association', []];
            }
        }

        $column = $this->inspector->attributeInstance($property, ORM\Column::class);
        if (null === $column) {
            return null;
        }

        if (isset($column->enumType) && is_string($column->enumType) && '' !== $column->enumType) {
            return ['enum', ['enumType' => $column->enumType]];
        }

        if ($this->fieldPolicy->looksLikeEmailField($fieldName)) {
            return ['email', []];
        }

        if ($this->fieldPolicy->looksLikeUrlField($fieldName)) {
            return ['url', []];
        }

        if ($this->fieldPolicy->looksLikeLongTextField($fieldName)) {
            return ['textarea', []];
        }

        $propertyType = $this->inspector->propertyTypeName($property);
        if ('array' === $propertyType && [] !== ($arrayChoiceFields[$fieldName] ?? [])) {
            return ['array_choice', ['choices' => $arrayChoiceFields[$fieldName]]];
        }

        if (null !== $propertyType && '' !== $propertyType) {
            return [$this->normalizePhpType($propertyType), []];
        }

        return [$this->normalizeDoctrineType($column->type ?? 'string', $column->length ?? null), []];
    }

    private function normalizePhpType(string $propertyType): string
    {
        return match ($propertyType) {
            'bool' => 'boolean',
            'int' => 'integer',
            'float' => 'number',
            \DateTimeImmutable::class, \DateTimeInterface::class, \DateTime::class => 'datetime',
            default => 'text',
        };
    }

    private function normalizeDoctrineType(?string $doctrineType, ?int $length): string
    {
        return match ($doctrineType ?? 'string') {
            'boolean' => 'boolean',
            'integer', 'smallint', 'bigint' => 'integer',
            'float', 'decimal' => 'number',
            'date', 'date_immutable' => 'date',
            'datetime', 'datetime_immutable' => 'datetime',
            'time', 'time_immutable' => 'time',
            'text' => 'textarea',
            default => null !== $length && $length > 255 ? 'textarea' : 'text',
        };
    }
}
