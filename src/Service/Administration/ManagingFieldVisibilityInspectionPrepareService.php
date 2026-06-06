<?php

declare(strict_types=1);

namespace App\Managing\Service\Administration;

use App\Managing\ServiceInterface\Administration\ManagingFieldVisibilityInspectionPrepareServiceInterface;
use App\Managing\Value\Administration\ManagingFieldVisibilityInspectionPrepareRequest;
use App\Managing\Value\Administration\ManagingFieldVisibilityInspectionPrepareResult;

/**
 * Prepares a concrete Managing field visibility inspection request from Administering.
 *
 * This service does not call Managing runtime directly. It emits a normalized payload that a host
 * integration or operator can submit to the Managing inspector/console command.
 */
final readonly class ManagingFieldVisibilityInspectionPrepareService implements ManagingFieldVisibilityInspectionPrepareServiceInterface
{
    /** @var list<string> */
    private const PAGE_NAMES = ['index', 'detail', 'new', 'edit'];

    public function prepare(ManagingFieldVisibilityInspectionPrepareRequest $request): ManagingFieldVisibilityInspectionPrepareResult
    {
        $error = $this->validate($request);
        if (null !== $error) {
            return ManagingFieldVisibilityInspectionPrepareResult::rejected($error);
        }

        $payload = $request->toManagingInspectionPayload();
        $warnings = [];

        if (null === $request->subjectIdentifier || '' === trim($request->subjectIdentifier)) {
            $warnings[] = 'No subject identifier was provided; Managing will inspect the anonymous/default visibility corridor.';
        }

        if (in_array($payload['pageName'], ['new', 'edit'], true)) {
            $warnings[] = 'Form-page inspection must still be interpreted with required/non-hideable field protection in Managing.';
        }

        return ManagingFieldVisibilityInspectionPrepareResult::accepted($payload, [
            'source' => 'administering_ui',
            'surface' => 'managing_field_visibility_inspection_prepare',
            'requested_by_subject' => $request->requestedBySubject,
            'resource_class' => $payload['resourceClass'],
            'field_name' => $payload['fieldName'],
            'page_name' => $payload['pageName'],
            'subject_identifier' => $payload['subjectIdentifier'],
            'reason' => $request->reason,
        ], $warnings);
    }

    private function validate(ManagingFieldVisibilityInspectionPrepareRequest $request): ?string
    {
        if (!str_contains(trim($request->resourceClass), '\\')) {
            return 'field_visibility_inspection_resource_class_fqcn_required';
        }

        if (1 !== preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', trim($request->fieldName))) {
            return 'field_visibility_inspection_invalid_field_name';
        }

        if (!in_array(strtolower(trim($request->pageName)), self::PAGE_NAMES, true)) {
            return 'field_visibility_inspection_invalid_page_name';
        }

        foreach ([$request->statusCandidates, $request->publicationFlagCandidates, $request->publicationDateCandidates] as $fields) {
            foreach ($fields as $field) {
                if (1 !== preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', trim($field))) {
                    return 'field_visibility_inspection_invalid_candidate_field_name';
                }
            }
        }

        return null;
    }
}
