<?php

declare(strict_types=1);

namespace App\Managing\Factory\Crud;

use App\Managing\Builder\Crud\ManageEasyAdminFieldBuilder;
use App\Managing\FactoryInterface\Crud\ManageCrudFieldDefinitionFactoryInterface;
use App\Managing\FactoryInterface\Crud\ManageEasyAdminFieldFromDefinitionFactoryInterface;
use App\Managing\Inspector\Crud\ManageEntityReflectionInspector;
use App\Managing\Policy\Crud\ManageCrudFieldPolicy;
use App\Managing\Resolver\Crud\ManageCrudFieldVisibilityResolver;
use App\Managing\ResolverInterface\Crud\ManageCrudFieldVisibilityResolverInterface;

/**
 * Orchestrates the ordered EasyAdmin field surface for generic Manage CRUDs.
 *
 * Wave 6 routes field rendering through neutral field definitions and local
 * visibility decisions before any EasyAdmin Field object is created.
 */
final class ManageCrudFieldFactory
{
    private readonly ManageCrudFieldDefinitionFactoryInterface $fieldDefinitionFactory;
    private readonly ManageCrudFieldVisibilityResolverInterface $fieldVisibilityResolver;
    private readonly ManageEasyAdminFieldFromDefinitionFactoryInterface $fieldFromDefinitionFactory;

    public function __construct(
        ?ManageEntityReflectionInspector $inspector = null,
        ?ManageCrudFieldPolicy $fieldPolicy = null,
        ?ManageEasyAdminFieldBuilder $fieldBuilder = null,
        ?ManageCrudFieldDefinitionFactoryInterface $fieldDefinitionFactory = null,
        ?ManageCrudFieldVisibilityResolverInterface $fieldVisibilityResolver = null,
        ?ManageEasyAdminFieldFromDefinitionFactoryInterface $fieldFromDefinitionFactory = null,
    ) {
        $inspector ??= new ManageEntityReflectionInspector();
        $fieldPolicy ??= new ManageCrudFieldPolicy();
        $fieldBuilder ??= new ManageEasyAdminFieldBuilder($inspector, fieldPolicy: $fieldPolicy);
        $this->fieldDefinitionFactory = $fieldDefinitionFactory ?? new ManageCrudFieldDefinitionFactory($inspector, $fieldPolicy);
        $this->fieldVisibilityResolver = $fieldVisibilityResolver ?? new ManageCrudFieldVisibilityResolver();
        $this->fieldFromDefinitionFactory = $fieldFromDefinitionFactory ?? new ManageEasyAdminFieldFromDefinitionFactory($fieldBuilder);
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
        ?string $subjectIdentifier = null,
    ): iterable {
        $definitions = $this->fieldDefinitionFactory->definitions(
            $entityFqcn,
            $pageName,
            $statusCandidates,
            $publicationFlagCandidates,
            $publicationDateCandidates,
            $arrayChoiceFields,
            $fieldTypeOverrides,
        );

        foreach ($definitions as $definition) {
            $decision = $this->fieldVisibilityResolver->decisionFor(
                $definition,
                $pageName,
                $subjectIdentifier,
            );
            if (!$decision->renderable()) {
                continue;
            }

            $field = $this->fieldFromDefinitionFactory->fieldForDefinition($definition);
            if (null !== $field) {
                yield $field;
            }
        }
    }
}
