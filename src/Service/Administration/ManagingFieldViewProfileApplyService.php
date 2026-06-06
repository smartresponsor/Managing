<?php

declare(strict_types=1);

namespace App\Managing\Service\Administration;

use App\Managing\ServiceInterface\Administration\ManagingFieldViewProfileApplyServiceInterface;
use App\Managing\Value\Administration\ManagingFieldViewProfileApplyRequest;
use App\Managing\Value\Administration\ManagingFieldViewProfileApplyResult;

/**
 * Prepares a reviewed Managing field view profile payload for the Managing apply handler.
 *
 * This service deliberately does not write to Managing storage. It validates the review context
 * and emits a payload that a host integration can submit to the Managing apply handler.
 */
final readonly class ManagingFieldViewProfileApplyService implements ManagingFieldViewProfileApplyServiceInterface
{
    public function prepare(ManagingFieldViewProfileApplyRequest $request): ManagingFieldViewProfileApplyResult
    {
        $contextError = $this->validateReviewContext($request->reviewContext);
        if (null !== $contextError) {
            return ManagingFieldViewProfileApplyResult::rejected($contextError);
        }

        $payloadError = $this->validateProfilePayload($request->normalizedProfilePayload);
        if (null !== $payloadError) {
            return ManagingFieldViewProfileApplyResult::rejected($payloadError);
        }

        $warnings = [];
        if ('merge' === ($request->reviewContext['mode'] ?? null)) {
            $warnings[] = 'Merge mode requires a Managing storage backend that can read the existing profile before applying changes.';
        }

        if (in_array($request->reviewContext['page_name'] ?? null, ['new', 'edit'], true)) {
            $warnings[] = 'Managing runtime must still protect required and non-hideable fields on form pages.';
        }

        return ManagingFieldViewProfileApplyResult::accepted([
            'normalized_profile_payload' => $request->normalizedProfilePayload,
            'review_context' => $request->reviewContext,
            'actor_identifier' => $request->requestedBySubject,
            'reason' => $request->reason ?? ($request->reviewContext['reason'] ?? null),
        ], $request->reviewContext, $warnings);
    }

    /** @param array<string, mixed> $reviewContext */
    private function validateReviewContext(array $reviewContext): ?string
    {
        if ('managing_field_view_profile_review' !== ($reviewContext['surface'] ?? null)) {
            return 'field_view_profile_apply_untrusted_surface';
        }

        if (!is_string($reviewContext['profile_permission'] ?? null) || !str_starts_with($reviewContext['profile_permission'], 'managing.field.profile.')) {
            return 'field_view_profile_apply_invalid_profile_permission';
        }

        if (!is_string($reviewContext['subject_key'] ?? null) || '' === trim($reviewContext['subject_key'])) {
            return 'field_view_profile_apply_subject_key_required';
        }

        if (!is_string($reviewContext['page_name'] ?? null) || '' === trim($reviewContext['page_name'])) {
            return 'field_view_profile_apply_page_name_required';
        }

        if (isset($reviewContext['mode']) && !in_array($reviewContext['mode'], ['replace', 'clear', 'merge'], true)) {
            return 'field_view_profile_apply_invalid_mode';
        }

        return null;
    }

    /** @param array<string, mixed> $payload */
    private function validateProfilePayload(array $payload): ?string
    {
        $subjects = $payload['subjects'] ?? null;
        if (!is_array($subjects) || 1 !== count($subjects)) {
            return 'field_view_profile_apply_requires_single_subject';
        }

        $subjectKey = (string) array_key_first($subjects);
        $subjectProfile = $subjects[$subjectKey] ?? null;
        if (!is_array($subjectProfile)) {
            return 'field_view_profile_apply_invalid_subject_profile';
        }

        if (isset($subjectProfile['resources'])) {
            $resources = $subjectProfile['resources'];
            if (!is_array($resources) || 1 !== count($resources)) {
                return 'field_view_profile_apply_requires_single_resource';
            }

            $resourceClass = (string) array_key_first($resources);
            if (!str_contains($resourceClass, '\\')) {
                return 'field_view_profile_apply_invalid_resource_class';
            }

            $pageRules = $resources[$resourceClass] ?? null;
        } else {
            $pageRules = $subjectProfile['defaults'] ?? null;
        }

        if (!is_array($pageRules) || 1 !== count($pageRules)) {
            return 'field_view_profile_apply_requires_single_page_rule';
        }

        $pageName = (string) array_key_first($pageRules);
        $rule = $pageRules[$pageName] ?? [];
        if (!is_array($rule)) {
            return 'field_view_profile_apply_invalid_page_rule';
        }

        $visible = $this->stringList($rule['visible'] ?? []);
        $hidden = $this->stringList($rule['hidden'] ?? []);
        if ([] !== array_intersect($visible, $hidden)) {
            return 'field_view_profile_apply_conflicting_field_preferences';
        }

        return null;
    }

    /** @return list<string> */
    private function stringList(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }

        $normalized = [];
        foreach ($values as $value) {
            if (is_string($value) && '' !== trim($value)) {
                $normalized[] = trim($value);
            }
        }

        return array_values(array_unique($normalized));
    }
}
