<?php

declare(strict_types=1);

namespace App\Managing\Handler\Crud;

use App\Managing\HandlerInterface\Crud\ManageCrudFieldUserProfileApplyHandlerInterface;
use App\Managing\ReaderInterface\Crud\ManageCrudFieldUserProfileReaderInterface;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileApplyRequest;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileApplyResult;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileWriteRequest;
use App\Managing\WriterInterface\Crud\ManageCrudFieldUserProfileWriterInterface;

final readonly class ManageCrudFieldUserProfileApplyHandler implements ManageCrudFieldUserProfileApplyHandlerInterface
{
    public function __construct(
        private ManageCrudFieldUserProfileWriterInterface $writer,
        private ?ManageCrudFieldUserProfileReaderInterface $reader = null,
    ) {
    }

    public function apply(ManageCrudFieldUserProfileApplyRequest $request): ManageCrudFieldUserProfileApplyResult
    {
        $contextValidation = $this->validateReviewContext($request->reviewContext);
        if (null !== $contextValidation) {
            return ManageCrudFieldUserProfileApplyResult::rejected($contextValidation);
        }

        $payloadRule = $this->extractSinglePayloadRule($request->normalizedProfilePayload);
        if (is_string($payloadRule)) {
            return ManageCrudFieldUserProfileApplyResult::rejected($payloadRule);
        }
        if ($this->mergeNeedsUnavailableReader($request, $payloadRule['subject_identifier'])) {
            return ManageCrudFieldUserProfileApplyResult::rejected('field_user_profile_merge_requires_storage_reader');
        }

        $writeResult = $this->writer->write(new ManageCrudFieldUserProfileWriteRequest(
            subjectIdentifier: $payloadRule['subject_identifier'],
            pageName: $payloadRule['page_name'],
            visibleFields: $payloadRule['visible_fields'],
            hiddenFields: $payloadRule['hidden_fields'],
            resourceClass: $payloadRule['resource_class'],
            actorIdentifier: $request->actorIdentifier,
            reason: $request->reason ?? $this->stringOrNull($request->reviewContext['reason'] ?? null),
        ));
        if (!$writeResult->accepted) {
            return ManageCrudFieldUserProfileApplyResult::rejected($writeResult->reason);
        }

        return ManageCrudFieldUserProfileApplyResult::accepted(
            $writeResult->normalizedProfileConfig,
            $writeResult->reason,
            $this->warningsFor($payloadRule['page_name']),
        );
    }

    /** @param array<string, mixed> $reviewContext */
    private function validateReviewContext(array $reviewContext): ?string
    {
        if ('managing_field_view_profile_review' !== ($reviewContext['surface'] ?? null)) {
            return 'field_user_profile_apply_untrusted_surface';
        }
        if (!is_string($reviewContext['profile_permission'] ?? null) || !str_starts_with($reviewContext['profile_permission'], 'managing.field.profile.')) {
            return 'field_user_profile_apply_invalid_profile_permission';
        }
        if (!is_string($reviewContext['subject_key'] ?? null) || '' === trim($reviewContext['subject_key'])) {
            return 'field_user_profile_apply_subject_key_required';
        }
        if (!is_string($reviewContext['page_name'] ?? null) || '' === trim($reviewContext['page_name'])) {
            return 'field_user_profile_apply_page_name_required';
        }
        if (isset($reviewContext['mode']) && !in_array($reviewContext['mode'], ['replace', 'clear', 'merge'], true)) {
            return 'field_user_profile_apply_invalid_mode';
        }

        return null;
    }

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

    private function mergeNeedsUnavailableReader(ManageCrudFieldUserProfileApplyRequest $request, string $subjectIdentifier): bool
    {
        if ('merge' !== ($request->reviewContext['mode'] ?? null)) {
            return false;
        }

        return null === $this->reader || !$this->reader->read($subjectIdentifier)->available;
    }

    /** @return list<string> */
    private function warningsFor(string $pageName): array
    {
        return in_array($pageName, ['new', 'edit'], true)
            ? ['Managing runtime still enforces required and non-hideable field protections on form pages.']
            : [];
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

    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && '' !== ($value = trim($value)) ? $value : null;
    }
}
