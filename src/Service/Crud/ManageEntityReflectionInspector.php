<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

final class ManageEntityReflectionInspector
{
    public function __construct(
        private readonly ManageEntityFieldAccessor $fieldAccessor = new ManageEntityFieldAccessor(),
        private readonly ManageEntityMetadataInspector $metadataInspector = new ManageEntityMetadataInspector(),
    ) {
    }

    public function hasField(string $entityFqcn, string $field): bool
    {
        return $this->fieldAccessor->hasField($entityFqcn, $field);
    }

    /**
     * @param list<string> $fields
     *
     * @return list<string>
     */
    public function existingFields(string $entityFqcn, array $fields): array
    {
        return $this->fieldAccessor->existingFields($entityFqcn, $fields);
    }

    public function readField(object $entity, string $field): mixed
    {
        return $this->fieldAccessor->readField($entity, $field);
    }

    public function writeField(string $entityFqcn, object $entity, string $field, mixed $value): bool
    {
        return $this->fieldAccessor->writeField($entityFqcn, $entity, $field, $value);
    }

    public function propertyTypeName(\ReflectionProperty $property): ?string
    {
        return $this->metadataInspector->propertyTypeName($property);
    }

    public function attributeInstance(\ReflectionProperty $property, string $attributeClass): ?object
    {
        return $this->metadataInspector->attributeInstance($property, $attributeClass);
    }

    public function enumTypeFor(string $entityFqcn, string $field): ?string
    {
        return $this->metadataInspector->enumTypeFor($entityFqcn, $field);
    }
}
