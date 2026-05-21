<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use App\Managing\Service\Policy\ManagePolicyValueNormalizer;

/**
 * Central policy for EasyAdmin field discovery.
 *
 * The generic CRUD layer still supports pragmatic name-based fallback because
 * Managing has to expose heterogeneous host application entities. The fallback
 * vocabulary is now explicit, configurable and testable instead of being hidden
 * inside the field factory.
 */
final class ManageCrudFieldPolicy
{
    /** @var list<string> */
    private readonly array $titleFields;

    /** @var list<string> */
    private readonly array $identityFields;

    /** @var list<string> */
    private readonly array $emailKeywords;

    /** @var list<string> */
    private readonly array $urlKeywords;

    /** @var list<string> */
    private readonly array $longTextKeywords;

    private readonly ManageCrudFieldTypeOverridePolicy $fieldTypeOverridePolicy;

    /**
     * @param list<string>                         $titleFields
     * @param list<string>                         $identityFields
     * @param list<string>                         $emailKeywords
     * @param list<string>                         $urlKeywords
     * @param list<string>                         $longTextKeywords
     * @param array<string, array<string, string>> $fieldTypeOverrides keyed by entity FQCN, then field name
     */
    public function __construct(
        array $titleFields = ['firstTitle', 'title', 'name', 'label'],
        array $identityFields = ['code', 'slug', 'sku'],
        array $emailKeywords = ['email'],
        array $urlKeywords = ['url', 'link'],
        array $longTextKeywords = ['description', 'summary', 'message', 'content', 'body', 'note', 'payload', 'details'],
        array $fieldTypeOverrides = [],
        ?ManageCrudFieldTypeOverridePolicy $fieldTypeOverridePolicy = null,
        private readonly ManagePolicyValueNormalizer $valueNormalizer = new ManagePolicyValueNormalizer(),
    ) {
        $this->fieldTypeOverridePolicy = $fieldTypeOverridePolicy ?? new ManageCrudFieldTypeOverridePolicy($fieldTypeOverrides, $this->valueNormalizer);
        $this->titleFields = $this->valueNormalizer->stringList($titleFields);
        $this->identityFields = $this->valueNormalizer->stringList($identityFields);
        $this->emailKeywords = $this->valueNormalizer->lowercaseStringList($emailKeywords);
        $this->urlKeywords = $this->valueNormalizer->lowercaseStringList($urlKeywords);
        $this->longTextKeywords = $this->valueNormalizer->lowercaseStringList($longTextKeywords);
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

    /** @param list<string> $runtimeExcludedFields */
    public function isDiscoveryExcludedField(string $fieldName, array $runtimeExcludedFields): bool
    {
        return in_array($fieldName, [
            'id',
            ...$this->titleFields,
            ...$this->identityFields,
            ...$runtimeExcludedFields,
            'description',
            'createdAt',
            'updatedAt',
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
