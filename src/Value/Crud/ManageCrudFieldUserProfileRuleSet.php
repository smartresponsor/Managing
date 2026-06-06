<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * Config-shaped personal view profile rules for Managing CRUD fields.
 *
 * This is a bridge format for the execution seam. A later persistence-backed resolver can replace
 * it without changing the EasyAdmin field pipeline.
 */
final readonly class ManageCrudFieldUserProfileRuleSet
{
    /** @param array<string, array{defaults: array<string, array<string, list<string>>>, resources: array<string, array<string, array<string, list<string>>>>}> $subjects */
    public function __construct(private array $subjects = [])
    {
    }

    /** @param array<string, mixed> $config */
    public static function fromArray(array $config): self
    {
        $subjects = [];
        $subjectProfiles = $config['subjects'] ?? [];
        if (!is_array($subjectProfiles)) {
            return new self();
        }

        foreach ($subjectProfiles as $subjectIdentifier => $profile) {
            if (!is_string($subjectIdentifier) || !is_array($profile)) {
                continue;
            }

            $subjectIdentifier = trim($subjectIdentifier);
            if ('' === $subjectIdentifier) {
                continue;
            }

            $subjects[$subjectIdentifier] = [
                'defaults' => self::normalizePageRules($profile['defaults'] ?? []),
                'resources' => self::normalizeResourceRules($profile['resources'] ?? []),
            ];
        }

        return new self($subjects);
    }

    public function decisionFor(ManageCrudFieldDefinition $definition, string $pageName, ?string $subjectIdentifier): ?ManageCrudFieldUserProfileDecision
    {
        if (null === $subjectIdentifier || '' === trim($subjectIdentifier)) {
            return null;
        }

        foreach ([$subjectIdentifier, '*'] as $subjectKey) {
            $profile = $this->subjects[$subjectKey] ?? null;
            if (null === $profile) {
                continue;
            }

            $resourceDecision = $this->decisionFromPageRules($profile['resources'][$definition->resourceClass] ?? [], $pageName, $definition->fieldName);
            if (null !== $resourceDecision) {
                return $resourceDecision;
            }

            $defaultDecision = $this->decisionFromPageRules($profile['defaults'], $pageName, $definition->fieldName);
            if (null !== $defaultDecision) {
                return $defaultDecision;
            }
        }

        return null;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['subjects' => $this->subjects];
    }

    /** @param mixed $rules @return array<string, array<string, list<string>>> */
    private static function normalizePageRules(mixed $rules): array
    {
        if (!is_array($rules)) {
            return [];
        }

        $normalized = [];
        foreach ($rules as $page => $pageRules) {
            if (!is_string($page) || !is_array($pageRules)) {
                continue;
            }

            $page = trim($page);
            if ('' === $page) {
                continue;
            }

            $normalized[$page] = [
                'visible' => self::stringList($pageRules['visible'] ?? []),
                'hidden' => self::stringList($pageRules['hidden'] ?? []),
            ];
        }

        return $normalized;
    }

    /** @param mixed $resources @return array<string, array<string, array<string, list<string>>>> */
    private static function normalizeResourceRules(mixed $resources): array
    {
        if (!is_array($resources)) {
            return [];
        }

        $normalized = [];
        foreach ($resources as $resourceClass => $pageRules) {
            if (!is_string($resourceClass)) {
                continue;
            }

            $resourceClass = trim($resourceClass);
            if ('' === $resourceClass) {
                continue;
            }

            $normalized[$resourceClass] = self::normalizePageRules($pageRules);
        }

        return $normalized;
    }

    /** @param mixed $values @return list<string> */
    private static function stringList(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }

        $normalized = [];
        foreach ($values as $value) {
            if (!is_string($value)) {
                continue;
            }

            $value = trim($value);
            if ('' !== $value && !in_array($value, $normalized, true)) {
                $normalized[] = $value;
            }
        }

        return $normalized;
    }

    /** @param array<string, array<string, list<string>>> $pageRules */
    private function decisionFromPageRules(array $pageRules, string $pageName, string $fieldName): ?ManageCrudFieldUserProfileDecision
    {
        foreach ([$pageName, 'all', '*'] as $page) {
            if (!isset($pageRules[$page])) {
                continue;
            }

            if (in_array($fieldName, $pageRules[$page]['visible'] ?? [], true)) {
                return ManageCrudFieldUserProfileDecision::visible($fieldName);
            }

            if (in_array($fieldName, $pageRules[$page]['hidden'] ?? [], true)) {
                return ManageCrudFieldUserProfileDecision::hidden($fieldName);
            }
        }

        return null;
    }
}
