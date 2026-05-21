<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

/**
 * Resolves entity-specific EasyAdmin surface metadata for generated Manage CRUD screens.
 *
 * This service keeps label humanization, field-candidate merging, existing-field
 * filtering and default-sort selection out of the base controller. Concrete CRUD
 * controllers still provide runtime candidates through protected hooks, while
 * the configured policy remains the generic fallback.
 */
final class ManageCrudEntitySurfaceResolver
{
    public function __construct(
        private readonly ManageCrudBehaviorPolicy $behaviorPolicy = new ManageCrudBehaviorPolicy(),
        private readonly ManageEntityReflectionInspector $entityInspector = new ManageEntityReflectionInspector(),
    ) {
    }

    /** @return array{singular: string, plural: string} */
    public function labels(string $entityFqcn, ?string $singularOverride = null, ?string $pluralOverride = null): array
    {
        $singular = $singularOverride ?? $this->humanizeEntityShortName($entityFqcn, false);

        return [
            'singular' => $singular,
            'plural' => $pluralOverride ?? $this->humanizeEntityShortName($entityFqcn, true),
        ];
    }

    /** @param list<string> $runtimeCandidates */
    public function searchFields(string $entityFqcn, array $runtimeCandidates = []): array
    {
        return $this->existingFields($entityFqcn, $this->searchFieldCandidates($runtimeCandidates));
    }

    /** @param list<string> $runtimeCandidates */
    public function searchFieldCandidates(array $runtimeCandidates = []): array
    {
        return $this->behaviorPolicy->searchFields($runtimeCandidates);
    }

    /** @param list<string> $runtimeCandidates */
    public function statusFields(string $entityFqcn, array $runtimeCandidates = []): array
    {
        return $this->existingFields($entityFqcn, $this->statusFieldCandidates($runtimeCandidates));
    }

    /** @param list<string> $runtimeCandidates */
    public function statusFieldCandidates(array $runtimeCandidates = []): array
    {
        return $this->behaviorPolicy->statusFields($runtimeCandidates);
    }

    /** @param list<string> $runtimeCandidates */
    public function publicationFlagFields(string $entityFqcn, array $runtimeCandidates = []): array
    {
        return $this->existingFields($entityFqcn, $this->publicationFlagFieldCandidates($runtimeCandidates));
    }

    /** @param list<string> $runtimeCandidates */
    public function publicationFlagFieldCandidates(array $runtimeCandidates = []): array
    {
        return $this->behaviorPolicy->publicationFlagFields($runtimeCandidates);
    }

    /** @param list<string> $runtimeCandidates */
    public function publicationDateFieldCandidates(array $runtimeCandidates = []): array
    {
        return $this->behaviorPolicy->publicationDateFields($runtimeCandidates);
    }

    /** @param list<string> $publicationDateRuntimeCandidates */
    public function filterDateFields(string $entityFqcn, array $publicationDateRuntimeCandidates = []): array
    {
        return $this->existingFields($entityFqcn, $this->behaviorPolicy->filterDateFields($publicationDateRuntimeCandidates));
    }

    /** @return array<string, string> */
    public function defaultSort(string $entityFqcn): array
    {
        foreach ($this->behaviorPolicy->defaultSortFields() as $field) {
            if ($this->entityInspector->hasField($entityFqcn, $field)) {
                return [$field => 'DESC'];
            }
        }

        return [];
    }

    /**
     * @param list<string> $fields
     *
     * @return list<string>
     */
    private function existingFields(string $entityFqcn, array $fields): array
    {
        return $this->entityInspector->existingFields($entityFqcn, $fields);
    }

    private function humanizeEntityShortName(string $entityFqcn, bool $plural): string
    {
        $shortName = (new \ReflectionClass($entityFqcn))->getShortName();
        if (str_starts_with($shortName, 'ManageCrud')) {
            $shortName = substr($shortName, strlen('ManageCrud'));
        }

        $words = (string) preg_replace('/(?<!^)[A-Z]/', ' $0', $shortName);

        if ($plural && !str_ends_with($words, 's')) {
            $words .= 's';
        }

        return $words;
    }
}
