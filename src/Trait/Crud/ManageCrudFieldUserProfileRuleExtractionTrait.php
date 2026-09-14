<?php

declare(strict_types=1);

namespace App\Managing\Trait\Crud;

trait ManageCrudFieldUserProfileRuleExtractionTrait
{
    /**
     * @param array<string, mixed> $payload
     *
     * @return array{subject_identifier: string, page_name: string, visible_fields: list<string>, hidden_fields: list<string>, resource_class: ?string}|string
     */
    private function extractSinglePayloadRule(array $payload): array|string
    {
        $subjects = $payload['subjects'] ?? null;
        if (!is_array($subjects) || 1 !== count($subjects)) {
            return 'field_user_profile_apply_requires_single_subject';
        }

        $subjectIdentifier = (string) array_key_first($subjects);
        $subjectProfile = $subjects[$subjectIdentifier] ?? null;
        if (!is_array($subjectProfile)) {
            return 'field_user_profile_apply_invalid_subject_profile';
        }
        if (isset($subjectProfile['resources'])) {
            return $this->extractSingleResourceRule($subjectIdentifier, $subjectProfile['resources']);
        }
        if (!is_array($subjectProfile['defaults'] ?? null)) {
            return 'field_user_profile_apply_requires_default_or_resource_rule';
        }

        return $this->extractSinglePageRule($subjectIdentifier, $subjectProfile['defaults'], null);
    }

    /** @return array{subject_identifier: string, page_name: string, visible_fields: list<string>, hidden_fields: list<string>, resource_class: ?string}|string */
    private function extractSingleResourceRule(string $subjectIdentifier, mixed $resources): array|string
    {
        if (!is_array($resources) || 1 !== count($resources)) {
            return 'field_user_profile_apply_requires_single_resource';
        }

        $resourceClass = (string) array_key_first($resources);
        $pageRules = $resources[$resourceClass] ?? null;
        if (!is_array($pageRules)) {
            return 'field_user_profile_apply_invalid_resource_profile';
        }

        return $this->extractSinglePageRule($subjectIdentifier, $pageRules, $resourceClass);
    }

    /**
     * @param array<string, mixed> $pageRules
     *
     * @return array{subject_identifier: string, page_name: string, visible_fields: list<string>, hidden_fields: list<string>, resource_class: ?string}|string
     */
    private function extractSinglePageRule(string $subjectIdentifier, array $pageRules, ?string $resourceClass): array|string
    {
        if (1 !== count($pageRules)) {
            return 'field_user_profile_apply_requires_single_page_rule';
        }

        $pageName = (string) array_key_first($pageRules);
        $rule = $pageRules[$pageName] ?? [];
        if (!is_array($rule)) {
            return 'field_user_profile_apply_invalid_page_rule';
        }

        $visibleFields = $this->stringList($rule['visible'] ?? []);
        $hiddenFields = $this->stringList($rule['hidden'] ?? []);
        if ([] !== array_values(array_intersect($visibleFields, $hiddenFields))) {
            return 'field_user_profile_apply_conflicting_field_preferences';
        }

        return [
            'subject_identifier' => $subjectIdentifier,
            'page_name' => $pageName,
            'visible_fields' => $visibleFields,
            'hidden_fields' => $hiddenFields,
            'resource_class' => $resourceClass,
        ];
    }

    /** @return list<string> */
    private function stringList(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }

        $normalized = [];
        foreach ($values as $value) {
            if (is_string($value) && '' !== ($value = trim($value)) && !in_array($value, $normalized, true)) {
                $normalized[] = $value;
            }
        }

        return $normalized;
    }
}
