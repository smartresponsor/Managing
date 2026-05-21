<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use Doctrine\ORM\Mapping as ORM;

final class ManageEntityMetadataInspector
{
    public function propertyTypeName(\ReflectionProperty $property): ?string
    {
        $type = $property->getType();
        if ($type instanceof \ReflectionNamedType) {
            return $type->getName();
        }

        if ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $namedType) {
                if ($namedType->allowsNull()) {
                    continue;
                }

                return $namedType->getName();
            }

            $namedTypes = $type->getTypes();
            if ([] !== $namedTypes) {
                return $namedTypes[0]->getName();
            }
        }

        return null;
    }

    public function attributeInstance(\ReflectionProperty $property, string $attributeClass): ?object
    {
        $attributes = $property->getAttributes($attributeClass);
        if ([] === $attributes) {
            return null;
        }

        try {
            return $attributes[0]->newInstance();
        } catch (\Throwable) {
            return null;
        }
    }

    public function enumTypeFor(string $entityFqcn, string $field): ?string
    {
        try {
            $reflection = new \ReflectionClass($entityFqcn);
            if (!$reflection->hasProperty($field)) {
                return null;
            }

            $property = $reflection->getProperty($field);
            $attributes = $property->getAttributes(ORM\Column::class);
            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();
                if (isset($instance->enumType) && is_string($instance->enumType) && '' !== $instance->enumType) {
                    return $instance->enumType;
                }
            }
        } catch (\ReflectionException) {
            return null;
        }

        return null;
    }
}
