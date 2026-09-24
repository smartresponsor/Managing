<?php

declare(strict_types=1);

namespace App\Managing\Service\Administration;

use App\Administering\Entity\AdministrationAclMutationReviewRecord;
use App\Administering\ServiceInterface\Rolling\AdministrationAclMutationApplyServiceInterface as HostApplyServiceInterface;
use App\Managing\RepositoryInterface\Administration\ManageAclMutationReviewRecordRepositoryInterface;
use App\Managing\ServiceInterface\Administration\ManageFieldAccessMutationApplyServiceInterface;
use App\Managing\Validator\Administration\ManageFieldAccessReviewValidator;
use App\Managing\Value\Administration\ManageFieldAccessMutationApplyResult;

/**
 * Applies only previously reviewed Managing field-access policy records.
 */
final readonly class ManageFieldAccessMutationApplyService implements ManageFieldAccessMutationApplyServiceInterface
{
    public function __construct(
        private ManageAclMutationReviewRecordRepositoryInterface $reviewRecordRepository,
        private HostApplyServiceInterface $aclMutationApplyService,
        private ManageFieldAccessReviewValidator $fieldAccessReviewValidator,
    ) {
    }

    public function applyReviewedFieldAccessMutation(string $requestKey, string $requestedBySubject): ManageFieldAccessMutationApplyResult
    {
        $requestKey = trim($requestKey);

        if ('' === $requestKey) {
            return ManageFieldAccessMutationApplyResult::skipped('', 'Managing field access review key is required.', [
                'reason' => 'missing_request_key',
                'surface' => 'managing_field_access_mutation_apply',
            ]);
        }

        $record = $this->reviewRecordRepository->findByRequestKey($requestKey);

        if (!$record instanceof AdministrationAclMutationReviewRecord) {
            return ManageFieldAccessMutationApplyResult::skipped($requestKey, 'Managing field access review record was not found.', [
                'reason' => 'missing_review_record',
                'surface' => 'managing_field_access_mutation_apply',
            ]);
        }

        if (!$this->fieldAccessReviewValidator->isManagingFieldAccessReview(
            $record->permissionOrRoleKey(),
            $record->scopeKey(),
            $record->mutationType(),
            $record->safeReviewPayload(),
        )) {
            return ManageFieldAccessMutationApplyResult::rejected($requestKey, 'Review record is not a Managing field access mutation review.', [
                'reason' => 'non_managing_field_access_review',
                'surface' => 'managing_field_access_mutation_apply',
                'permission_or_role_key' => $record->permissionOrRoleKey(),
                'scope_key' => $record->scopeKey(),
                'mutation_type' => $record->mutationType(),
            ]);
        }

        if (!$record->valid()) {
            return ManageFieldAccessMutationApplyResult::rejected($requestKey, 'Managing field access review is invalid and cannot be applied.', [
                'reason' => 'invalid_review_record',
                'surface' => 'managing_field_access_mutation_apply',
                'permission_or_role_key' => $record->permissionOrRoleKey(),
                'scope_key' => $record->scopeKey(),
            ]);
        }

        $result = $this->aclMutationApplyService->applyReviewedMutation($requestKey, $requestedBySubject);

        return ManageFieldAccessMutationApplyResult::fromRollingResult(
            $result->requestKey(),
            $result->succeeded(),
            $result->status(),
            $result->safeMessage(),
            $result->safeContext(),
        );
    }
}
