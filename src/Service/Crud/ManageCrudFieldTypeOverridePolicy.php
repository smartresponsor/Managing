<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use App\Managing\Service\Policy\ManagePolicyValueNormalizer;

/**
 * Owns explicit EasyAdmin field type override resolution.
 *
 * This keeps the main field policy focused on discovery vocabulary and makes
 * entity-specific field type overrides independently testable/configurable.
 */
final class ManageCrudFieldTypeOverridePolicy
{
    private const SUPPORTED_TYPES = [
        'text',
        'textarea',
        'email',
        'url',
        'boolean',
        'integer',
        'number',
        'date',
        'datetime',
        'time',
    ];

    /** @var array<string, array<string, string>> */
    private readonly array $fieldTypeOverrides;

    /**
     * @param array<string, mixed> $fieldTypeOverrides keyed by entity FQCN, then field name
     */
    public function __construct(
        array $fieldTypeOverrides = [],
        private readonly ManagePolicyValueNormalizer $valueNormalizer = new ManagePolicyValueNormalizer(),
    ) {
        $this->fieldTypeOverrides = $this->normalizeFieldTypeOverrides($fieldTypeOverrides);
    }

    /** @param array<string, array<string, string>|string> $runtimeOverrides */
    public function explicitFieldType(string $entityFqcn, string $fieldName, array $runtimeOverrides = []): ?string
    {
        $runtimeFieldType = $this->fieldTypeFromOverrides($runtimeOverrides, $entityFqcn, $fieldName);
        if (null !== $runtimeFieldType) {
            return $runtimeFieldType;
        }

        return $this->fieldTypeOverrides[$entityFqcn][$fieldName]
            ?? $this->fieldTypeOverrides['*'][$fieldName]
            ?? null;
    }

    /** @param array<string, array<string, string>|string> $overrides */
    private function fieldTypeFromOverrides(array $overrides, string $entityFqcn, string $fieldName): ?string
    {
        $fieldType = null;

        if (isset($overrides[$fieldName]) && is_string($overrides[$fieldName])) {
            $fieldType = $overrides[$fieldName];
        }

        if (isset($overrides['*']) && is_array($overrides['*']) && isset($overrides['*'][$fieldName])) {
            $fieldType = $overrides['*'][$fieldName];
        }

        if (isset($overrides[$entityFqcn]) && is_array($overrides[$entityFqcn]) && isset($overrides[$entityFqcn][$fieldName])) {
            $fieldType = $overrides[$entityFqcn][$fieldName];
        }

        return is_string($fieldType) ? $this->normalizeFieldType($fieldType) : null;
    }

    /**
     * @param array<string, mixed> $overrides
     *
     * @return array<string, array<string, string>>
     */
    private function normalizeFieldTypeOverrides(array $overrides): array
    {
        $normalized = [];
        foreach ($overrides as $entityFqcn => $fields) {
            if (!is_string($entityFqcn) || !is_array($fields)) {
                continue;
            }

            $normalizedEntityFqcn = trim($entityFqcn);
            if ('' === $normalizedEntityFqcn) {
                continue;
            }

            foreach ($fields as $fieldName => $fieldType) {
                if (!is_string($fieldName) || !is_string($fieldType)) {
                    continue;
                }

                $normalizedFieldName = trim($fieldName);
                $normalizedFieldType = $this->normalizeFieldType($fieldType);
                if ('' !== $normalizedFieldName && null !== $normalizedFieldType) {
                    $normalized[$normalizedEntityFqcn][$normalizedFieldName] = $normalizedFieldType;
                }
            }
        }

        return $normalized;
    }

    private function normalizeFieldType(string $fieldType): ?string
    {
        $fieldType = strtolower(trim($fieldType));

        return in_array($fieldType, self::SUPPORTED_TYPES, true) ? $fieldType : null;
    }
}
