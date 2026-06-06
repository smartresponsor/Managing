<?php

declare(strict_types=1);

namespace App\Managing\Inspector\Crud;

use App\Managing\FactoryInterface\Crud\ManageCrudFieldDefinitionFactoryInterface;
use App\Managing\InspectorInterface\Crud\ManageCrudFieldVisibilityInspectorInterface;
use App\Managing\ResolverInterface\Crud\ManageCrudFieldVisibilityExplanationResolverInterface;
use App\Managing\Value\Crud\ManageCrudFieldAccessContext;
use App\Managing\Value\Crud\ManageCrudFieldDefinition;
use App\Managing\Value\Crud\ManageCrudFieldVisibilityInspectionReport;
use App\Managing\Value\Crud\ManageCrudFieldVisibilityInspectionRequest;

/**
 * Builds an explainable diagnostic report for one concrete CRUD field.
 */
final class ManageCrudFieldVisibilityInspector implements ManageCrudFieldVisibilityInspectorInterface
{
    private const KNOWN_PAGES = [
        ManageCrudFieldAccessContext::PAGE_INDEX,
        ManageCrudFieldAccessContext::PAGE_DETAIL,
        ManageCrudFieldAccessContext::PAGE_NEW,
        ManageCrudFieldAccessContext::PAGE_EDIT,
    ];

    public function __construct(
        private readonly ManageCrudFieldDefinitionFactoryInterface $definitionFactory,
        private readonly ManageCrudFieldVisibilityExplanationResolverInterface $explanationResolver,
    ) {
    }

    public function inspect(ManageCrudFieldVisibilityInspectionRequest $request): ManageCrudFieldVisibilityInspectionReport
    {
        $definition = $this->definitionFor($request);
        if (null === $definition) {
            return new ManageCrudFieldVisibilityInspectionReport(
                $request,
                reason: 'field_definition_not_found',
            );
        }

        return new ManageCrudFieldVisibilityInspectionReport(
            $request,
            $this->explanationResolver->explainFor(
                $definition,
                $request->pageName,
                $request->subjectIdentifier,
            ),
        );
    }

    private function definitionFor(ManageCrudFieldVisibilityInspectionRequest $request): ?ManageCrudFieldDefinition
    {
        foreach ($this->candidatePages($request->pageName) as $pageName) {
            foreach ($this->definitionsFor($request, $pageName) as $definition) {
                if ($definition->fieldName === $request->fieldName) {
                    return $definition;
                }
            }
        }

        return null;
    }

    /** @return list<string> */
    private function candidatePages(string $pageName): array
    {
        return array_values(array_unique([$pageName, ...self::KNOWN_PAGES]));
    }

    /** @return list<ManageCrudFieldDefinition> */
    private function definitionsFor(ManageCrudFieldVisibilityInspectionRequest $request, string $pageName): array
    {
        return $this->definitionFactory->definitions(
            $request->resourceClass,
            $pageName,
            $request->statusCandidates,
            $request->publicationFlagCandidates,
            $request->publicationDateCandidates,
            $request->arrayChoiceFields,
            $request->fieldTypeOverrides,
        );
    }
}
