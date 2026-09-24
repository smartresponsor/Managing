<?php

declare(strict_types=1);

namespace App\Managing\Service\Administration;

use App\Managing\ServiceInterface\Administration\ManageFieldAccessMutationReviewServiceInterface;
use App\Managing\ValidatorInterface\Administration\ManageFieldAccessPolicyDescriptorValidatorInterface;
use App\Managing\Value\Administration\ManageFieldAccessMutationReviewInput;
use App\Managing\Value\Administration\ManageFieldAccessMutationReviewResult;
use App\Managing\Value\Administration\ManageFieldAccessPolicyDescriptor;

final readonly class ManageFieldAccessMutationReviewService implements ManageFieldAccessMutationReviewServiceInterface
{
    private const REVIEW_BUILDER_SERVICE = 'App\\Rolling\\ServiceInterface\\Administration\\RollingAclMutationReviewBuilderInterface';
    private const MUTATION_REQUEST_CLASS = 'App\\Rolling\\Value\\Administration\\RollingAclMutationRequest';
    private const FIELD_ACCESS_REQUEST_CLASS = 'App\\Rolling\\Value\\Administration\\RollingFieldAccessDecisionRequest';
    private const FIELD_ACCESS_SCOPE_SET_CLASS = 'App\\Rolling\\Value\\Administration\\RollingFieldAccessScopeSet';

    public function __construct(
        private ManageFieldAccessPolicyDescriptorValidatorInterface $descriptorValidator,
        private ?object $reviewBuilder = null,
    ) {
    }

    public function review(ManageFieldAccessMutationReviewInput $input): ManageFieldAccessMutationReviewResult
    {
        $this->descriptorValidator->assertValid($input->descriptor);
        $mutationRequest = $this->toRollingMutationRequest($input);
        $reviewBuilder = $this->reviewBuilder();
        $review = $reviewBuilder->review($mutationRequest);
        if (!is_object($review) || !method_exists($review, 'toSafeArray')) {
            throw new \LogicException('Rolling ACL mutation review builder returned an unsupported review payload.');
        }

        $safeReview = $review->toSafeArray();
        if (!is_array($safeReview)) {
            throw new \LogicException('Rolling ACL mutation review must expose a safe array payload.');
        }

        return new ManageFieldAccessMutationReviewResult($input->descriptor, $safeReview);
    }

    private function toRollingMutationRequest(ManageFieldAccessMutationReviewInput $input): object
    {
        $this->assertRollingReviewClassesAvailable();
        $descriptor = $input->descriptor;
        $fieldRequestClass = self::FIELD_ACCESS_REQUEST_CLASS;
        $fieldRequest = new $fieldRequestClass(
            permissionKey: $descriptor->permissionKey,
            componentKey: $descriptor->target->componentKey,
            resourceClass: $descriptor->target->resourceClass,
            fieldName: $descriptor->target->fieldName,
            pageName: $descriptor->target->pageName,
            operation: $descriptor->target->operation,
            subjectIdentifier: $this->subjectIdentifier($descriptor),
            attributes: $descriptor->target->attributes,
        );

        $scopeSetClass = self::FIELD_ACCESS_SCOPE_SET_CLASS;
        $scopeSet = $scopeSetClass::fromRequest($fieldRequest);
        $scope = $scopeSet->mostSpecificScope();
        if ('' === $scope) {
            throw new \LogicException('Rolling field access scope must be a non-empty string.');
        }

        $mutationRequestClass = self::MUTATION_REQUEST_CLASS;

        return new $mutationRequestClass(
            $this->mutationType($descriptor),
            $this->subjectIdentifier($descriptor),
            $descriptor->permissionKey,
            $scope,
            $input->requestedBySubject,
            $input->toSafeContext(),
        );
    }

    private function reviewBuilder(): object
    {
        if (null === $this->reviewBuilder || !method_exists($this->reviewBuilder, 'review')) {
            throw new \LogicException(sprintf('Optional Rolling review service %s is not available.', self::REVIEW_BUILDER_SERVICE));
        }

        return $this->reviewBuilder;
    }

    private function assertRollingReviewClassesAvailable(): void
    {
        foreach ([self::MUTATION_REQUEST_CLASS, self::FIELD_ACCESS_REQUEST_CLASS, self::FIELD_ACCESS_SCOPE_SET_CLASS] as $className) {
            if (!class_exists($className)) {
                throw new \LogicException(sprintf('Optional Rolling review class %s is not available.', $className));
            }
        }
    }

    private function mutationType(ManageFieldAccessPolicyDescriptor $descriptor): string
    {
        if (ManageFieldAccessPolicyDescriptor::SUBJECT_ROLE === $descriptor->subjectType) {
            return $descriptor->allows() ? 'permission.grant' : 'permission.revoke';
        }

        return $descriptor->allows() ? 'acl.allow' : 'acl.deny';
    }

    private function subjectIdentifier(ManageFieldAccessPolicyDescriptor $descriptor): string
    {
        $identifier = trim($descriptor->subjectIdentifier);

        if (ManageFieldAccessPolicyDescriptor::SUBJECT_ROLE === $descriptor->subjectType) {
            return $identifier;
        }

        if (str_contains($identifier, ':')) {
            return $identifier;
        }

        return sprintf('%s:%s', $descriptor->subjectType, $identifier);
    }
}
