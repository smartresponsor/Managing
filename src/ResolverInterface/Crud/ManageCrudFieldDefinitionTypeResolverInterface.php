<?php

declare(strict_types=1);

namespace App\Managing\ResolverInterface\Crud;

interface ManageCrudFieldDefinitionTypeResolverInterface
{
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
    ): ?array;
}
