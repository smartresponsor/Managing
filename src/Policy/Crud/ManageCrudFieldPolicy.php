<?php

declare(strict_types=1);

namespace App\Managing\Policy\Crud;

use App\Managing\DependencyInjection\ManagingCrudBehaviorConfigurationDefaults;
use App\Managing\DependencyInjection\ManagingCrudFieldConfigurationDefaults;
use App\Managing\Normalizer\Policy\ManagePolicyValueNormalizer;

/**
 * Central policy for EasyAdmin field discovery.
 *
 * The generic CRUD layer still supports pragmatic nameEntity-based fallback because
 * Managing has to expose heterogeneous host application entities. The fallback
 * vocabulary is now explicit, configurable and testable instead of being hidden
 * inside controllers or EasyAdmin field factories.
 */
final class ManageCrudFieldPolicy
{
    /** @var list<string> */
    private readonly array $primaryIdentifierFields;

    /** @var list<string> */
    private readonly array $titleFields;

    /** @var list<string> */
    private readonly array $identityFields;

    /** @var list<string> */
    private readonly array $descriptionFields;

    /** @var list<string> */
    private readonly array $technicalExcludedFields;

    /** @var list<string> */
    private readonly array $auditDateFields;

    /** @var list<string> */
    private readonly array $emailKeywords;

    /** @var list<string> */
    private readonly array $urlKeywords;

    /** @var list<string> */
    private readonly array $longTextKeywords;

    private readonly ManageCrudFieldTypeOverridePolicy $fieldTypeOverridePolicy;

    /**
     * @param list<string>|null                    $primaryIdentifierFields
     * @param list<string>|null                    $titleFields
     * @param list<string>|null                    $identityFields
     * @param list<string>|null                    $descriptionFields
     * @param list<string>|null                    $technicalExcludedFields
     * @param list<string>|null                    $auditDateFields
     * @param list<string>|null                    $emailKeywords
     * @param list<string>|null                    $urlKeywords
     * @param list<string>|null                    $longTextKeywords
     * @param array<string, array<string, string>> $fieldTypeOverrides      keyed by entity FQCN, then field nameEntity
     */
    public function __construct(
        ?array $primaryIdentifierFields = null,
        ?array $titleFields = null,
        ?array $identityFields = null,
        ?array $descriptionFields = null,
        ?array $technicalExcludedFields = null,
        ?array $auditDateFields = null,
        ?array $emailKeywords = null,
        ?array $urlKeywords = null,
        ?array $longTextKeywords = null,
        array $fieldTypeOverrides = [],
        ?ManageCrudFieldTypeOverridePolicy $fieldTypeOverridePolicy = null,
        private readonly ManagePolicyValueNormalizer $valueNormalizer = new ManagePolicyValueNormalizer(),
    ) {
        $this->fieldTypeOverridePolicy = $fieldTypeOverridePolicy ?? new ManageCrudFieldTypeOverridePolicy($fieldTypeOverrides, $this->valueNormalizer);
        $this->primaryIdentifierFields = $this->valueNormalizer->stringList($primaryIdentifierFields ?? ManagingCrudFieldConfigurationDefaults::primaryIdentifierCandidates());
        $this->titleFields = $this->valueNormalizer->stringList($titleFields ?? ManagingCrudFieldConfigurationDefaults::titleCandidates());
        $this->identityFields = $this->valueNormalizer->stringList($identityFields ?? ManagingCrudFieldConfigurationDefaults::identityCandidates());
        $this->descriptionFields = $this->valueNormalizer->stringList($descriptionFields ?? ManagingCrudFieldConfigurationDefaults::descriptionCandidates());
        $this->technicalExcludedFields = $this->valueNormalizer->stringList($technicalExcludedFields ?? ManagingCrudFieldConfigurationDefaults::technicalExcludedFields());
        $this->auditDateFields = $this->valueNormalizer->stringList($auditDateFields ?? ManagingCrudBehaviorConfigurationDefaults::auditDateFields());
        $this->emailKeywords = $this->valueNormalizer->lowercaseStringList($emailKeywords ?? ManagingCrudFieldConfigurationDefaults::emailKeywords());
        $this->urlKeywords = $this->valueNormalizer->lowercaseStringList($urlKeywords ?? ManagingCrudFieldConfigurationDefaults::urlKeywords());
        $this->longTextKeywords = $this->valueNormalizer->lowercaseStringList($longTextKeywords ?? ManagingCrudFieldConfigurationDefaults::longTextKeywords());
    }

    /** @return list<string> */
    public function primaryIdentifierFields(): array
    {
        return $this->primaryIdentifierFields;
    }

    /** @return list<string> */
    public function titleFields(): array
    {
        return $this->titleFields;
    }

    /** @return list<string> */
    public function identityFields(): array
    {
        return $this->identityFields;
    }

    /** @return list<string> */
    public function descriptionFields(): array
    {
        return $this->descriptionFields;
    }

    /** @return list<string> */
    public function auditDateFields(): array
    {
        return $this->auditDateFields;
    }

    /** @return list<string> */
    public function technicalExcludedFields(): array
    {
        return $this->technicalExcludedFields;
    }

    /** @param list<string> $runtimeExcludedFields */
    public function isDiscoveryExcludedField(string $fieldName, array $runtimeExcludedFields): bool
    {
        return in_array($fieldName, [
            ...$this->primaryIdentifierFields,
            ...$this->titleFields,
            ...$this->identityFields,
            ...$this->descriptionFields,
            ...$this->auditDateFields,
            ...$this->technicalExcludedFields,
            ...$runtimeExcludedFields,
        ], true);
    }

    /** @param array<string, array<string, string>|string> $runtimeOverrides */
    public function explicitFieldType(string $entityFqcn, string $fieldName, array $runtimeOverrides = []): ?string
    {
        return $this->fieldTypeOverridePolicy->explicitFieldType($entityFqcn, $fieldName, $runtimeOverrides);
    }

    public function looksLikeEmailField(string $fieldName): bool
    {
        return $this->containsAnyKeyword($fieldName, $this->emailKeywords);
    }

    public function looksLikeUrlField(string $fieldName): bool
    {
        return $this->containsAnyKeyword($fieldName, $this->urlKeywords);
    }

    public function looksLikeLongTextField(string $fieldName): bool
    {
        return $this->containsAnyKeyword($fieldName, $this->longTextKeywords);
    }

    /** @param list<string> $keywords */
    private function containsAnyKeyword(string $fieldName, array $keywords): bool
    {
        $fieldName = strtolower($fieldName);

        foreach ($keywords as $keyword) {
            if ('' !== $keyword && str_contains($fieldName, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
