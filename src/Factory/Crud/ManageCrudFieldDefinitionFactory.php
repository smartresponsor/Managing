<?php

declare(strict_types=1);

namespace App\Managing\Factory\Crud;

use App\Managing\FactoryInterface\Crud\ManageCrudFieldDefinitionFactoryInterface;
use App\Managing\Inspector\Crud\ManageEntityReflectionInspector;
use App\Managing\Labeler\Crud\ManageCrudFieldLabeler;
use App\Managing\Policy\Crud\ManageCrudFieldPolicy;
use App\Managing\Resolver\Crud\ManageCrudFieldDefinitionTypeResolver;
use App\Managing\ResolverInterface\Crud\ManageCrudFieldDefinitionTypeResolverInterface;
use App\Managing\Value\Crud\ManageCrudFieldAccessContext;
use App\Managing\Value\Crud\ManageCrudFieldDefinition;

/**
 * Creates neutral field definitions before EasyAdmin Field objects are built.
 */
final class ManageCrudFieldDefinitionFactory implements ManageCrudFieldDefinitionFactoryInterface
{
    private const COMPONENT_KEY = 'Managing';
    private const INDEX_PAGE = [ManageCrudFieldAccessContext::PAGE_INDEX];
    private const ALL_PAGES = [
        ManageCrudFieldAccessContext::PAGE_INDEX,
        ManageCrudFieldAccessContext::PAGE_DETAIL,
        ManageCrudFieldAccessContext::PAGE_NEW,
        ManageCrudFieldAccessContext::PAGE_EDIT,
    ];
    private const READ_PAGES = [ManageCrudFieldAccessContext::PAGE_INDEX, ManageCrudFieldAccessContext::PAGE_DETAIL];
    private const FORM_PAGES = [ManageCrudFieldAccessContext::PAGE_NEW, ManageCrudFieldAccessContext::PAGE_EDIT];

    public function __construct(
        private readonly ManageEntityReflectionInspector $inspector = new ManageEntityReflectionInspector(),
        private readonly ManageCrudFieldPolicy $fieldPolicy = new ManageCrudFieldPolicy(),
        private readonly ManageCrudFieldLabeler $labeler = new ManageCrudFieldLabeler(),
        private readonly ManageCrudFieldDefinitionTypeResolverInterface $typeResolver = new ManageCrudFieldDefinitionTypeResolver(),
    ) {
    }

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
    ): array {
        $definitions = [
            ...$this->candidateDefinitions($entityFqcn, $statusCandidates, $publicationFlagCandidates, $publicationDateCandidates),
            ...$this->discoverFormDefinitions($entityFqcn, $statusCandidates, $publicationFlagCandidates, $publicationDateCandidates, $arrayChoiceFields, $fieldTypeOverrides),
            ...$this->auditDefinitions($entityFqcn),
        ];

        return array_values(array_filter(
            $definitions,
            static fn (ManageCrudFieldDefinition $definition): bool => $definition->isAvailableOn($pageName),
        ));
    }

    /**
     * @param list<string> $statusCandidates
     * @param list<string> $publicationFlagCandidates
     * @param list<string> $publicationDateCandidates
     *
     * @return list<ManageCrudFieldDefinition>
     */
    private function candidateDefinitions(string $entityFqcn, array $statusCandidates, array $publicationFlagCandidates, array $publicationDateCandidates): array
    {
        return [
            ...$this->firstDefinition($entityFqcn, $this->fieldPolicy->primaryIdentifierFields(), 'identifier', self::INDEX_PAGE, false),
            ...$this->firstDefinition($entityFqcn, $this->fieldPolicy->titleFields(), 'title', self::ALL_PAGES, false),
            ...$this->definitionsFor($entityFqcn, $this->fieldPolicy->identityFields(), 'identity', self::ALL_PAGES),
            ...$this->firstDefinition($entityFqcn, $statusCandidates, 'status', self::ALL_PAGES),
            ...$this->firstDefinition($entityFqcn, $publicationFlagCandidates, 'publication_flag', self::ALL_PAGES),
            ...$this->firstDefinition($entityFqcn, $publicationDateCandidates, 'publication_date', self::READ_PAGES),
            ...$this->firstDefinition($entityFqcn, $this->fieldPolicy->descriptionFields(), 'description', [
                ManageCrudFieldAccessContext::PAGE_DETAIL,
                ManageCrudFieldAccessContext::PAGE_NEW,
                ManageCrudFieldAccessContext::PAGE_EDIT,
            ]),
        ];
    }

    /**
     * @param list<string>                                $statusCandidates
     * @param list<string>                                $publicationFlagCandidates
     * @param list<string>                                $publicationDateCandidates
     * @param array<string, array<string, string>>        $arrayChoiceFields
     * @param array<string, array<string, string>|string> $fieldTypeOverrides
     *
     * @return list<ManageCrudFieldDefinition>
     */
    private function discoverFormDefinitions(string $entityFqcn, array $statusCandidates, array $publicationFlagCandidates, array $publicationDateCandidates, array $arrayChoiceFields, array $fieldTypeOverrides): array
    {
        try {
            $reflection = new \ReflectionClass($entityFqcn);
        } catch (\ReflectionException) {
            return [];
        }

        $definitions = [];
        $runtimeExcludedFields = [...$statusCandidates, ...$publicationFlagCandidates, ...$publicationDateCandidates];
        foreach ($reflection->getProperties() as $property) {
            if ($property->isStatic() || $this->fieldPolicy->isDiscoveryExcludedField($property->getName(), $runtimeExcludedFields)) {
                continue;
            }

            $resolved = $this->typeResolver->typeForProperty($entityFqcn, $property, $arrayChoiceFields, $fieldTypeOverrides);
            if (null === $resolved) {
                continue;
            }

            $definitions[] = $this->definition($entityFqcn, $property->getName(), $resolved[0], self::FORM_PAGES, true, $resolved[1]);
        }

        return $definitions;
    }

    /** @return list<ManageCrudFieldDefinition> */
    private function auditDefinitions(string $entityFqcn): array
    {
        return $this->definitionsFor($entityFqcn, $this->fieldPolicy->auditDateFields(), 'audit_date', self::READ_PAGES);
    }

    /** @param list<string> $candidates @param list<string> $availableOn @return list<ManageCrudFieldDefinition> */
    private function definitionsFor(string $entityFqcn, array $candidates, string $fieldType, array $availableOn): array
    {
        return array_map(fn (string $field): ManageCrudFieldDefinition => $this->definition($entityFqcn, $field, $fieldType, $availableOn), $this->inspector->existingFields($entityFqcn, $candidates));
    }

    /** @param list<string> $candidates @param list<string> $availableOn @return list<ManageCrudFieldDefinition> */
    private function firstDefinition(string $entityFqcn, array $candidates, string $fieldType, array $availableOn, bool $hideable = true): array
    {
        $fields = $this->inspector->existingFields($entityFqcn, $candidates);

        return [] === $fields ? [] : [$this->definition($entityFqcn, $fields[0], $fieldType, $availableOn, $hideable)];
    }

    /** @param list<string> $availableOn @param array<string, mixed> $options */
    private function definition(string $entityFqcn, string $fieldName, string $fieldType, array $availableOn, bool $hideable = true, array $options = []): ManageCrudFieldDefinition
    {
        return new ManageCrudFieldDefinition(self::COMPONENT_KEY, $entityFqcn, $fieldName, $this->labeler->labelFor($fieldName), $fieldType, $availableOn, $hideable, options: $options);
    }
}
