<?php

declare(strict_types=1);

namespace App\Managing\Service\Administration;

use App\Administering\Entity\AdministrationAclMutationReviewRecord;
use App\Administering\ServiceInterface\Rolling\AdministrationAclMutationApplyServiceInterface as HostApplyServiceInterface;
use App\Managing\ServiceInterface\Administration\ManagingFieldAccessMutationApplyServiceInterface;
use App\Managing\Validator\Administration\ManagingFieldAccessReviewValidator;
use App\Managing\Value\Administration\ManagingFieldAccessMutationApplyResult;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Applies only previously reviewed Managing field-access policy records.
 */
final readonly class ManagingFieldAccessMutationApplyService implements ManagingFieldAccessMutationApplyServiceInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private HostApplyServiceInterface $aclMutationApplyService,
        private ManagingFieldAccessReviewValidator $fieldAccessReviewValidator,
    ) {
    }

    public function applyReviewedFieldAccessMutation(string $requestKey, string $requestedBySubject): ManagingFieldAccessMutationApplyResult
    {
        $requestKey = trim($requestKey);

        if ('' === $requestKey) {
            return ManagingFieldAccessMutationApplyResult::skipped('', 'Managing field access review key is required.', [
                'reason' => 'missing_request_key',
                'surface' => 'managing_field_access_mutation_apply',
            ]);
        }

        $record = $this->reviewRecord($requestKey);

        if (!$record instanceof AdministrationAclMutationReviewRecord) {
            return ManagingFieldAccessMutationApplyResult::skipped($requestKey, 'Managing field access review record was not found.', [
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
            return ManagingFieldAccessMutationApplyResult::rejected($requestKey, 'Review record is not a Managing field access mutation review.', [
                'reason' => 'non_managing_field_access_review',
                'surface' => 'managing_field_access_mutation_apply',
                'permission_or_role_key' => $record->permissionOrRoleKey(),
                'scope_key' => $record->scopeKey(),
                'mutation_type' => $record->mutationType(),
            ]);
        }

        if (!$record->valid()) {
            return ManagingFieldAccessMutationApplyResult::rejected($requestKey, 'Managing field access review is invalid and cannot be applied.', [
                'reason' => 'invalid_review_record',
                'surface' => 'managing_field_access_mutation_apply',
                'permission_or_role_key' => $record->permissionOrRoleKey(),
                'scope_key' => $record->scopeKey(),
            ]);
        }

        $result = $this->aclMutationApplyService->applyReviewedMutation($requestKey, $requestedBySubject);

        return ManagingFieldAccessMutationApplyResult::fromRollingResult(
            $result->requestKey(),
            $result->succeeded(),
            $result->status(),
            $result->safeMessage(),
            $result->safeContext(),
        );
    }

    private function reviewRecord(string $requestKey): ?AdministrationAclMutationReviewRecord
    {
        $manager = $this->managerRegistry->getManagerForClass(AdministrationAclMutationReviewRecord::class);

        if (null === $manager) {
            throw new \LogicException('No Doctrine manager is configured for Administering ACL mutation review records. Configure the system SQLite entity manager for App\\Administering entities.');
        }

        $record = $manager
            ->getRepository(AdministrationAclMutationReviewRecord::class)
            ->findOneBy(['requestKey' => $requestKey]);

        return $record instanceof AdministrationAclMutationReviewRecord ? $record : null;
    }
}
