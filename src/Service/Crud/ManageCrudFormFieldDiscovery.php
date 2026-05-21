<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

/**
 * Discovers form fields for generic Manage CRUD create/edit pages.
 *
 * Keeping reflection iteration outside ManageCrudFieldFactory prevents the
 * factory from becoming responsible for both screen ordering and entity
 * property traversal.
 */
final class ManageCrudFormFieldDiscovery
{
    public function __construct(
        private readonly ManageCrudFieldPolicy $fieldPolicy = new ManageCrudFieldPolicy(),
        private readonly ManageEasyAdminFieldBuilder $fieldBuilder = new ManageEasyAdminFieldBuilder(),
    ) {
    }

    /**
     * @param list<string>                                $statusCandidates
     * @param list<string>                                $publicationFlagCandidates
     * @param list<string>                                $publicationDateCandidates
     * @param array<string, array<string, string>>        $arrayChoiceFields
     * @param array<string, array<string, string>|string> $fieldTypeOverrides
     *
     * @return list<object>
     */
    public function discover(
        string $entityFqcn,
        string $pageName,
        array $statusCandidates,
        array $publicationFlagCandidates,
        array $publicationDateCandidates,
        array $arrayChoiceFields = [],
        array $fieldTypeOverrides = [],
    ): array {
        if (!\in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT], true)) {
            return [];
        }

        try {
            $reflection = new \ReflectionClass($entityFqcn);
        } catch (\ReflectionException) {
            return [];
        }

        $fields = [];
        $runtimeExcludedFields = $this->runtimeExcludedFields($statusCandidates, $publicationFlagCandidates, $publicationDateCandidates);

        foreach ($reflection->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            if ($this->fieldPolicy->isDiscoveryExcludedField($property->getName(), $runtimeExcludedFields)) {
                continue;
            }

            $field = $this->fieldBuilder->formFieldForProperty($property, $arrayChoiceFields, $fieldTypeOverrides);
            if (null !== $field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @param list<string> $statusCandidates
     * @param list<string> $publicationFlagCandidates
     * @param list<string> $publicationDateCandidates
     *
     * @return list<string>
     */
    private function runtimeExcludedFields(array $statusCandidates, array $publicationFlagCandidates, array $publicationDateCandidates): array
    {
        return [
            ...$statusCandidates,
            ...$publicationFlagCandidates,
            ...$publicationDateCandidates,
        ];
    }
}
