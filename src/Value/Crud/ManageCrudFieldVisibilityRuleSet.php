<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * Backend-configured field visibility rules for Managing CRUD surfaces.
 *
 * The rules are intentionally neutral: they do not know EasyAdmin classes and
 * they do not replace Rolling access decisions. They only express component or
 * host-application presentation policy before EasyAdmin field objects are built.
 */
final readonly class ManageCrudFieldVisibilityRuleSet
{
    /** @param array<string, array<string, list<string>>> $defaults @param array<string, array<string, array<string, list<string>>>> $resources */
    public function __construct(
        private array $defaults = [],
        private array $resources = [],
    ) {
    }

    /** @param array<string, mixed> $config */
    public static function fromArray(array $config): self
    {
        return new self(
            self::normalizePageRules($config['defaults'] ?? []),
            self::normalizeResourceRules($config['resources'] ?? []),
        );
    }

    public function decisionFor(ManageCrudFieldDefinition $definition, string $pageName): ?ManageCrudFieldVisibilityDecision
    {
        $resourceRules = $this->resources[$definition->resourceClass] ?? [];
        $resourceDecision = $this->decisionFromPageRules($resourceRules, $pageName, $definition->fieldName, 'resource');
        if (null !== $resourceDecision) {
            return $resourceDecision;
        }

        return $this->decisionFromPageRules($this->defaults, $pageName, $definition->fieldName, 'default');
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'defaults' => $this->defaults,
            'resources' => $this->resources,
        ];
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

            $normalizedPage = trim($page);
            if ('' === $normalizedPage) {
                continue;
            }

            $normalized[$normalizedPage] = [
                'visible' => self::stringList($pageRules['visible'] ?? []),
                'hidden' => self::stringList($pageRules['hidden'] ?? []),
                'denied' => self::stringList($pageRules['denied'] ?? []),
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
    private function decisionFromPageRules(array $pageRules, string $pageName, string $fieldName, string $scope): ?ManageCrudFieldVisibilityDecision
    {
        foreach ([$pageName, 'all', '*'] as $page) {
            if (!isset($pageRules[$page])) {
                continue;
            }

            $decision = $this->decisionFromRule($pageRules[$page], $fieldName, $scope);
            if (null !== $decision) {
                return $decision;
            }
        }

        return null;
    }

    /** @param array<string, list<string>> $rule */
    private function decisionFromRule(array $rule, string $fieldName, string $scope): ?ManageCrudFieldVisibilityDecision
    {
        if (in_array($fieldName, $rule['denied'] ?? [], true)) {
            return new ManageCrudFieldVisibilityDecision($fieldName, false, false, ManageCrudFieldVisibilityDecision::SOURCE_SYSTEM, $scope.'_configured_denied');
        }

        if (in_array($fieldName, $rule['visible'] ?? [], true)) {
            return new ManageCrudFieldVisibilityDecision($fieldName, true, true, ManageCrudFieldVisibilityDecision::SOURCE_SYSTEM, $scope.'_configured_visible');
        }

        if (in_array($fieldName, $rule['hidden'] ?? [], true)) {
            return new ManageCrudFieldVisibilityDecision($fieldName, true, false, ManageCrudFieldVisibilityDecision::SOURCE_SYSTEM, $scope.'_configured_hidden');
        }

        return null;
    }
}
