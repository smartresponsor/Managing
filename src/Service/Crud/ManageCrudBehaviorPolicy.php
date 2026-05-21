<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use App\Managing\Service\Policy\ManagePolicyValueNormalizer;

/**
 * Central policy for generic Manage CRUD surface candidates.
 *
 * The policy keeps the default search, status, publication and sort field
 * vocabularies out of the controller. Concrete generated CRUD controllers may
 * still provide runtime candidates through protected hooks when a component
 * needs a stricter domain override.
 */
final class ManageCrudBehaviorPolicy
{
    /** @var list<string> */
    private readonly array $searchFields;

    /** @var list<string> */
    private readonly array $statusFields;

    /** @var list<string> */
    private readonly array $publicationFlagFields;

    /** @var list<string> */
    private readonly array $publicationDateFields;

    /** @var list<string> */
    private readonly array $auditDateFields;

    /** @var list<string> */
    private readonly array $defaultSortFields;

    /**
     * @param list<string> $searchFields
     * @param list<string> $statusFields
     * @param list<string> $publicationFlagFields
     * @param list<string> $publicationDateFields
     * @param list<string> $auditDateFields
     * @param list<string> $defaultSortFields
     */
    public function __construct(
        array $searchFields = ['firstTitle', 'title', 'name', 'label', 'code', 'slug', 'sku', 'status', 'state'],
        array $statusFields = ['status', 'state'],
        array $publicationFlagFields = ['published', 'isPublished', 'enabled', 'active'],
        array $publicationDateFields = ['publishedAt', 'publishAt'],
        array $auditDateFields = ['createdAt', 'updatedAt'],
        array $defaultSortFields = ['updatedAt', 'createdAt', 'publishedAt', 'id'],
        private readonly ManagePolicyValueNormalizer $valueNormalizer = new ManagePolicyValueNormalizer(),
    ) {
        $this->searchFields = $this->valueNormalizer->stringList($searchFields);
        $this->statusFields = $this->valueNormalizer->stringList($statusFields);
        $this->publicationFlagFields = $this->valueNormalizer->stringList($publicationFlagFields);
        $this->publicationDateFields = $this->valueNormalizer->stringList($publicationDateFields);
        $this->auditDateFields = $this->valueNormalizer->stringList($auditDateFields);
        $this->defaultSortFields = $this->valueNormalizer->stringList($defaultSortFields);
    }

    /** @param list<string> $runtimeCandidates */
    public function searchFields(array $runtimeCandidates = []): array
    {
        return $this->runtimeOrDefault($runtimeCandidates, $this->searchFields);
    }

    /** @param list<string> $runtimeCandidates */
    public function statusFields(array $runtimeCandidates = []): array
    {
        return $this->runtimeOrDefault($runtimeCandidates, $this->statusFields);
    }

    /** @param list<string> $runtimeCandidates */
    public function publicationFlagFields(array $runtimeCandidates = []): array
    {
        return $this->runtimeOrDefault($runtimeCandidates, $this->publicationFlagFields);
    }

    /** @param list<string> $runtimeCandidates */
    public function publicationDateFields(array $runtimeCandidates = []): array
    {
        return $this->runtimeOrDefault($runtimeCandidates, $this->publicationDateFields);
    }

    /** @param list<string> $runtimeCandidates */
    public function filterDateFields(array $runtimeCandidates = []): array
    {
        return array_values(array_unique([
            ...$this->auditDateFields,
            ...$this->publicationDateFields($runtimeCandidates),
        ]));
    }

    /** @return list<string> */
    public function defaultSortFields(): array
    {
        return $this->defaultSortFields;
    }

    /**
     * @param list<string> $runtimeCandidates
     * @param list<string> $defaultCandidates
     *
     * @return list<string>
     */
    private function runtimeOrDefault(array $runtimeCandidates, array $defaultCandidates): array
    {
        $runtimeCandidates = $this->valueNormalizer->stringList($runtimeCandidates);

        return [] !== $runtimeCandidates ? $runtimeCandidates : $defaultCandidates;
    }
}
