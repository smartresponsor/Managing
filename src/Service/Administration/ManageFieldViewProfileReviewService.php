<?php

declare(strict_types=1);

namespace App\Managing\Service\Administration;

use App\Managing\ServiceInterface\Administration\ManageFieldViewProfileReviewServiceInterface;
use App\Managing\Value\Administration\ManageFieldPermissionVocabulary;
use App\Managing\Value\Administration\ManageFieldViewProfileEditRequest;
use App\Managing\Value\Administration\ManageFieldViewProfileReviewResult;

/**
 * Builds safe review payloads for Managing field view profile edits.
 *
 * The service intentionally does not persist or apply the profile. It only normalizes the payload that a later
 * controlled workflow can store in system configuration/storage.
 */
final readonly class ManageFieldViewProfileReviewService implements ManageFieldViewProfileReviewServiceInterface
{
    /** @var list<string> */
    private const SUBJECT_TYPES = [
        ManageFieldViewProfileEditRequest::SUBJECT_USER,
        ManageFieldViewProfileEditRequest::SUBJECT_ROLE,
        ManageFieldViewProfileEditRequest::SUBJECT_GROUP,
    ];

    /** @var list<string> */
    private const PAGE_NAMES = ['index', 'detail', 'new', 'edit', 'all', '*'];

    public function review(ManageFieldViewProfileEditRequest $request): ManageFieldViewProfileReviewResult
    {
        $this->assertValidRequest($request);

        $visible = $this->normalizeFields($request->visibleFields);
        $hidden = $this->normalizeFields($request->hiddenFields);
        $conflicts = array_values(array_intersect($visible, $hidden));

        if ([] !== $conflicts) {
            throw new \InvalidArgumentException(sprintf('Fields cannot be both visible and hidden: %s.', implode(', ', $conflicts)));
        }

        $subjectKey = $request->subjectKey();
        $pageName = $this->normalizePageName($request->pageName);
        $rule = array_filter([
            'visible' => $visible,
            'hidden' => $hidden,
        ], static fn (array $fields): bool => [] !== $fields);

        $payload = ['subjects' => [$subjectKey => []]];

        if ($request->targetsResource()) {
            $payload['subjects'][$subjectKey]['resources'] = [
                trim((string) $request->resourceClass) => [
                    $pageName => $rule,
                ],
            ];
            $targetReference = sprintf('Managing field view profile:%s:resource:%s:page:%s', $subjectKey, trim((string) $request->resourceClass), $pageName);
        } else {
            $payload['subjects'][$subjectKey]['defaults'] = [
                $pageName => $rule,
            ];
            $targetReference = sprintf('Managing field view profile:%s:defaults:page:%s', $subjectKey, $pageName);
        }

        $warnings = [];
        if ('edit' === $pageName || 'new' === $pageName) {
            $warnings[] = 'Managing runtime must still protect required and non-hideable fields on form pages.';
        }

        if ([] === $rule) {
            $warnings[] = 'Empty visible/hidden rule means a later apply workflow should clear this profile page rule.';
        }

        return new ManageFieldViewProfileReviewResult(
            request: $request,
            changeType: 'managing.field_view_profile.review',
            targetReference: $targetReference,
            normalizedProfilePayload: $payload,
            reviewContext: [
                'source' => 'administering_ui',
                'surface' => 'managing_field_view_profile_review',
                'requested_by_subject' => $request->requestedBySubject,
                'subject_type' => $request->subjectType,
                'subject_key' => $subjectKey,
                'profile_permission' => $this->permissionForSubjectType($request->subjectType),
                'mode' => $request->mode,
                'reason' => $request->reason,
                'resource_class' => $request->targetsResource() ? trim((string) $request->resourceClass) : null,
                'page_name' => $pageName,
            ],
            warnings: $warnings,
        );
    }

    private function assertValidRequest(ManageFieldViewProfileEditRequest $request): void
    {
        if (!in_array($request->subjectType, self::SUBJECT_TYPES, true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported field view profile subject type "%s".', $request->subjectType));
        }

        if ('' === trim($request->subjectIdentifier)) {
            throw new \InvalidArgumentException('Field view profile subject identifier cannot be empty.');
        }

        if (!in_array($this->normalizePageName($request->pageName), self::PAGE_NAMES, true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported field view profile page "%s".', $request->pageName));
        }

        if (!in_array($request->mode, ['replace', 'merge', 'clear'], true)) {
            throw new \InvalidArgumentException(sprintf('Unsupported field view profile mode "%s".', $request->mode));
        }

        if ($request->targetsResource()) {
            $resourceClass = trim((string) $request->resourceClass);

            if (!str_contains($resourceClass, '\\')) {
                throw new \InvalidArgumentException('Resource-specific field view profile review requires a fully qualified resource class.');
            }
        }
    }

    /**
     * @param list<string> $fields
     *
     * @return list<string>
     */
    private function normalizeFields(array $fields): array
    {
        $normalized = [];

        foreach ($fields as $field) {
            $field = trim($field);

            if ('' === $field) {
                continue;
            }

            if (1 !== preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $field)) {
                throw new \InvalidArgumentException(sprintf('Invalid field nameEntity "%s".', $field));
            }

            $normalized[$field] = $field;
        }

        return array_values($normalized);
    }

    private function normalizePageName(string $pageName): string
    {
        $pageName = strtolower(trim($pageName));

        return 'default' === $pageName ? 'all' : $pageName;
    }

    private function permissionForSubjectType(string $subjectType): string
    {
        return match ($subjectType) {
            ManageFieldViewProfileEditRequest::SUBJECT_ROLE => ManageFieldPermissionVocabulary::PROFILE_ROLE_UPDATE,
            ManageFieldViewProfileEditRequest::SUBJECT_GROUP => ManageFieldPermissionVocabulary::PROFILE_GROUP_UPDATE,
            default => ManageFieldPermissionVocabulary::PROFILE_USER_UPDATE,
        };
    }
}
