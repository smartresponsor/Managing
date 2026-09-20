<?php

declare(strict_types=1);

namespace App\Managing\Handler\Crud;

use App\Managing\HandlerInterface\Crud\ManageCrudFieldUserProfileApplyHandlerInterface;
use App\Managing\ReaderInterface\Crud\ManageCrudFieldUserProfileReaderInterface;
use App\Managing\Trait\Crud\ManageCrudFieldUserProfileRuleExtractionTrait;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileApplyRequest;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileApplyResult;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileWriteRequest;
use App\Managing\WriterInterface\Crud\ManageCrudFieldUserProfileWriterInterface;

final readonly class ManageCrudFieldUserProfileApplyHandler implements ManageCrudFieldUserProfileApplyHandlerInterface
{
    use ManageCrudFieldUserProfileRuleExtractionTrait;

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

    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && '' !== ($value = trim($value)) ? $value : null;
    }
}
