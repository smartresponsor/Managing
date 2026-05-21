<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

final class ManageEntityFieldAccessor
{
    public function hasField(string $entityFqcn, string $field): bool
    {
        try {
            $reflection = new \ReflectionClass($entityFqcn);
        } catch (\ReflectionException) {
            return false;
        }

        if ($reflection->hasProperty($field)) {
            return true;
        }

        $suffix = ucfirst($field);

        return $reflection->hasMethod('get'.$suffix)
            || $reflection->hasMethod('is'.$suffix)
            || $reflection->hasMethod('has'.$suffix);
    }

    /**
     * @param list<string> $fields
     *
     * @return list<string>
     */
    public function existingFields(string $entityFqcn, array $fields): array
    {
        return array_values(array_filter($fields, fn (string $field): bool => $this->hasField($entityFqcn, $field)));
    }

    public function readField(object $entity, string $field): mixed
    {
        $suffix = ucfirst($field);
        foreach (['get'.$suffix, 'is'.$suffix, 'has'.$suffix] as $method) {
            if (method_exists($entity, $method)) {
                return $entity->{$method}();
            }
        }

        try {
            $property = new \ReflectionProperty($entity, $field);
            if (!$property->isPublic()) {
                $property->setAccessible(true);
            }

            return $property->getValue($entity);
        } catch (\ReflectionException) {
            return null;
        }
    }

    public function writeField(string $entityFqcn, object $entity, string $field, mixed $value): bool
    {
        if (!$this->hasField($entityFqcn, $field)) {
            return false;
        }

        $suffix = ucfirst($field);
        foreach (['set'.$suffix, 'mark'.$suffix] as $method) {
            if (method_exists($entity, $method)) {
                $entity->{$method}($value);

                return true;
            }
        }

        try {
            $property = new \ReflectionProperty($entity, $field);
            if (!$property->isPublic()) {
                $property->setAccessible(true);
            }
            $property->setValue($entity, $value);

            return true;
        } catch (\ReflectionException) {
            return false;
        }
    }
}
