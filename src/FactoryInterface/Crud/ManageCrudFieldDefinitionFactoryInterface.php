<?php

declare(strict_types=1);

namespace App\Managing\FactoryInterface\Crud;

use App\Managing\Value\Crud\ManageCrudFieldDefinition;

interface ManageCrudFieldDefinitionFactoryInterface
{
    /**
     * @param list<string>                                $statusCandidates
     * @param list<string>                                $publicationFlagCandidates
     * @param list<string>                                $publicationDateCandidates
     * @param array<string, array<string, string>>        $arrayChoiceFields
     * @param array<string, array<string, string>|string> $fieldTypeOverrides
     *
     * @return list<ManageCrudFieldDefinition>
     */
    public function definitions(
        string $entityFqcn,
        string $pageName,
        array $statusCandidates = [],
        array $publicationFlagCandidates = [],
        array $publicationDateCandidates = [],
        array $arrayChoiceFields = [],
        array $fieldTypeOverrides = [],
    ): array;
}
