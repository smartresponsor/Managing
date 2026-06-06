<?php

declare(strict_types=1);

namespace App\Managing\Service\Administration;

use App\Managing\ServiceInterface\Administration\ManagingFieldAccessMutationReviewServiceInterface;
use App\Managing\ValidatorInterface\Administration\ManagingFieldAccessPolicyDescriptorValidatorInterface;
use App\Managing\Value\Administration\ManagingFieldAccessMutationReviewInput;
use App\Managing\Value\Administration\ManagingFieldAccessMutationReviewResult;
use App\Managing\Value\Administration\ManagingFieldAccessPolicyDescriptor;
use App\Rolling\ServiceInterface\Administration\RollingAclMutationReviewBuilderInterface;
use App\Rolling\Value\Administration\RollingAclMutationRequest;
use App\Rolling\Value\Administration\RollingFieldAccessDecisionRequest;
use App\Rolling\Value\Administration\RollingFieldAccessScopeSet;

final readonly class ManagingFieldAccessMutationReviewService implements ManagingFieldAccessMutationReviewServiceInterface
{
    public function __construct(
        private RollingAclMutationReviewBuilderInterface $reviewBuilder,
        private ManagingFieldAccessPolicyDescriptorValidatorInterface $descriptorValidator,
    ) {
    }

    public function review(ManagingFieldAccessMutationReviewInput $input): ManagingFieldAccessMutationReviewResult
    {
        $this->descriptorValidator->assertValid($input->descriptor);
        $mutationRequest = $this->toRollingMutationRequest($input);
        $review = $this->reviewBuilder->review($mutationRequest);

        return new ManagingFieldAccessMutationReviewResult($input->descriptor, $review);
    }

    private function toRollingMutationRequest(ManagingFieldAccessMutationReviewInput $input): RollingAclMutationRequest
    {
        $descriptor = $input->descriptor;
        $scope = RollingFieldAccessScopeSet::fromRequest(new RollingFieldAccessDecisionRequest(
            permissionKey: $descriptor->permissionKey,
            componentKey: $descriptor->target->componentKey,
            resourceClass: $descriptor->target->resourceClass,
            fieldName: $descriptor->target->fieldName,
            pageName: $descriptor->target->pageName,
            operation: $descriptor->target->operation,
            subjectIdentifier: $this->subjectIdentifier($descriptor),
            attributes: $descriptor->target->attributes,
        ))->mostSpecificScope();

        return new RollingAclMutationRequest(
            $this->mutationType($descriptor),
            $this->subjectIdentifier($descriptor),
            $descriptor->permissionKey,
            $scope,
            $input->requestedBySubject,
            $input->toSafeContext(),
        );
    }

    private function mutationType(ManagingFieldAccessPolicyDescriptor $descriptor): string
    {
        if (ManagingFieldAccessPolicyDescriptor::SUBJECT_ROLE === $descriptor->subjectType) {
            return $descriptor->allows() ? 'permission.grant' : 'permission.revoke';
        }

        return $descriptor->allows() ? 'acl.allow' : 'acl.deny';
    }

    private function subjectIdentifier(ManagingFieldAccessPolicyDescriptor $descriptor): string
    {
        $identifier = trim($descriptor->subjectIdentifier);

        if (ManagingFieldAccessPolicyDescriptor::SUBJECT_ROLE === $descriptor->subjectType) {
            return $identifier;
        }

        if (str_contains($identifier, ':')) {
            return $identifier;
        }

        return sprintf('%s:%s', $descriptor->subjectType, $identifier);
    }
}
