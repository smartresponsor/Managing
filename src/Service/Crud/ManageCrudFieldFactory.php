<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

/**
 * Orchestrates the ordered EasyAdmin field surface for generic Manage CRUDs.
 *
 * Field type mapping is delegated to ManageEasyAdminFieldBuilder and naming
 * heuristics are delegated to ManageCrudFieldPolicy. This keeps the factory as
 * an ordering/discovery coordinator rather than a second god object.
 */
final class ManageCrudFieldFactory
{
    private readonly ManageCrudFormFieldDiscovery $formFieldDiscovery;

    public function __construct(
        private readonly ManageEntityReflectionInspector $inspector = new ManageEntityReflectionInspector(),
        private readonly ManageCrudFieldPolicy $fieldPolicy = new ManageCrudFieldPolicy(),
        private readonly ManageEasyAdminFieldBuilder $fieldBuilder = new ManageEasyAdminFieldBuilder(),
        ?ManageCrudFormFieldDiscovery $formFieldDiscovery = null,
    ) {
        $this->formFieldDiscovery = $formFieldDiscovery ?? new ManageCrudFormFieldDiscovery($this->fieldPolicy, $this->fieldBuilder);
    }

    /**
     * @param list<string>                                $statusCandidates
     * @param list<string>                                $publicationFlagCandidates
     * @param list<string>                                $publicationDateCandidates
     * @param array<string, array<string, string>>        $arrayChoiceFields
     * @param array<string, array<string, string>|string> $fieldTypeOverrides
     *
     * @return iterable<int, object>
     */
    public function fields(
        string $entityFqcn,
        string $pageName,
        array $statusCandidates,
        array $publicationFlagCandidates,
        array $publicationDateCandidates,
        array $arrayChoiceFields = [],
        array $fieldTypeOverrides = [],
    ): iterable {
        if ($this->inspector->hasField($entityFqcn, 'id')) {
            yield $this->fieldBuilder->idField();
        }

        foreach ($this->firstExistingField($entityFqcn, $this->fieldPolicy->titleFields()) as $field) {
            yield $this->fieldBuilder->titleField($field);
        }

        foreach ($this->existingFields($entityFqcn, $this->fieldPolicy->identityFields()) as $field) {
            yield $this->fieldBuilder->identityField($field);
        }

        foreach ($this->firstExistingField($entityFqcn, $statusCandidates) as $field) {
            yield $this->fieldBuilder->statusField($entityFqcn, $field);
        }

        foreach ($this->firstExistingField($entityFqcn, $publicationFlagCandidates) as $field) {
            yield $this->fieldBuilder->publicationFlagField($field);
        }

        foreach ($this->firstExistingField($entityFqcn, $publicationDateCandidates) as $field) {
            yield $this->fieldBuilder->publicationDateField($field);
        }

        if ($this->inspector->hasField($entityFqcn, 'description')) {
            yield $this->fieldBuilder->descriptionField();
        }

        foreach ($this->formFieldDiscovery->discover($entityFqcn, $pageName, $statusCandidates, $publicationFlagCandidates, $publicationDateCandidates, $arrayChoiceFields, $fieldTypeOverrides) as $field) {
            yield $field;
        }

        if ($this->inspector->hasField($entityFqcn, 'createdAt')) {
            yield $this->fieldBuilder->auditDateField('createdAt', 'Created');
        }

        if ($this->inspector->hasField($entityFqcn, 'updatedAt')) {
            yield $this->fieldBuilder->auditDateField('updatedAt', 'Updated');
        }
    }

    /**
     * @param list<string> $candidates
     *
     * @return list<string>
     */
    private function existingFields(string $entityFqcn, array $candidates): array
    {
        return $this->inspector->existingFields($entityFqcn, $candidates);
    }

    /**
     * @param list<string> $candidates
     *
     * @return list<string>
     */
    private function firstExistingField(string $entityFqcn, array $candidates): array
    {
        $fields = $this->existingFields($entityFqcn, $candidates);

        return [] === $fields ? [] : [$fields[0]];
    }
}
