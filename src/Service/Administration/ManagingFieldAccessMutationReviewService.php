<?php

declare(strict_types=1);

namespace App\Managing\Service\Administration;

use App\Managing\ServiceInterface\Administration\ManagingFieldAccessMutationReviewServiceInterface;
use App\Managing\ValidatorInterface\Administration\ManagingFieldAccessPolicyDescriptorValidatorInterface;
use App\Managing\Value\Administration\ManagingFieldAccessMutationReviewInput;
use App\Managing\Value\Administration\ManagingFieldAccessMutationReviewResult;
use App\Managing\Value\Administration\ManagingFieldAccessPolicyDescriptor;

final readonly class ManagingFieldAccessMutationReviewService implements ManagingFieldAccessMutationReviewServiceInterface
{
    private const REVIEW_BUILDER_SERVICE = 'App\\Rolling\\ServiceInterface\\Administration\\RollingAclMutationReviewBuilderInterface';
    private const MUTATION_REQUEST_CLASS = 'App\\Rolling\\Value\\Administration\\RollingAclMutationRequest';
    private const FIELD_ACCESS_REQUEST_CLASS = 'App\\Rolling\\Value\\Administration\\RollingFieldAccessDecisionRequest';
    private const FIELD_ACCESS_SCOPE_SET_CLASS = 'App\\Rolling\\Value\\Administration\\RollingFieldAccessScopeSet';

    public function __construct(
        private ManagingFieldAccessPolicyDescriptorValidatorInterface $descriptorValidator,
        private ?object $reviewBuilder = null,
    ) {
    }

    public function review(ManagingFieldAccessMutationReviewInput $input): ManagingFieldAccessMutationReviewResult
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

        return new ManagingFieldAccessMutationReviewResult($input->descriptor, $safeReview);
    }

    private function toRollingMutationRequest(ManagingFieldAccessMutationReviewInput $input): object
    {
        $this->assertRollingReviewClassesAvailable();
        $descriptor = $input->descriptor;
        $fieldRequestClass = self::FIELD_ACCESS_REQUEST_CLASS;
        // @phpstan-ignore class.notFound
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
        if (!is_object($scopeSet) || !method_exists($scopeSet, 'mostSpecificScope')) {
            throw new \LogicException('Rolling field access scope set contract is unavailable.');
        }
        $scope = $scopeSet->mostSpecificScope();
        if (!is_string($scope) || '' === $scope) {
            throw new \LogicException('Rolling field access scope must be a non-empty string.');
        }

        $mutationRequestClass = self::MUTATION_REQUEST_CLASS;

        // @phpstan-ignore class.notFound
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
