<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Administration;

use App\Managing\Validator\Administration\ManageFieldAccessReviewValidator;
use App\Managing\Value\Administration\ManageFieldPermissionVocabulary;
use PHPUnit\Framework\TestCase;

final class ManageFieldAccessReviewValidatorTest extends TestCase
{
    public function testMalformedSafeContextIsRejectedFailClosed(): void
    {
        self::assertFalse($this->validator()->isManagingFieldAccessReview(
            ManageFieldPermissionVocabulary::FIELD_VIEW,
            'component:managing',
            'permission.grant',
            ['safe_context' => 'malformed'],
        ));
    }

    public function testMalformedTargetIsRejectedFailClosed(): void
    {
        self::assertFalse($this->validator()->isManagingFieldAccessReview(
            ManageFieldPermissionVocabulary::FIELD_VIEW,
            'component:managing',
            'permission.grant',
            ['safe_context' => ['target' => 'malformed']],
        ));
    }

    public function testExplicitForeignComponentTargetIsRejected(): void
    {
        self::assertFalse($this->validator()->isManagingFieldAccessReview(
            ManageFieldPermissionVocabulary::FIELD_VIEW,
            'component:managing',
            'permission.grant',
            ['safe_context' => ['target' => ['component' => 'Cataloging']]],
        ));
    }

    public function testExplicitManagingComponentTargetIsAcceptedCaseInsensitively(): void
    {
        self::assertTrue($this->validator()->isManagingFieldAccessReview(
            ManageFieldPermissionVocabulary::FIELD_VIEW,
            'component:managing',
            'permission.grant',
            ['safe_context' => ['target' => ['component' => 'MANAGING']]],
        ));
    }

    public function testCanonicalManagingSurfaceIsAccepted(): void
    {
        self::assertTrue($this->validator()->isManagingFieldAccessReview(
            ManageFieldPermissionVocabulary::FIELD_VIEW,
            'component:managing',
            'acl.allow',
            ['safe_context' => ['surface' => 'managing_field_access_mutation_review']],
        ));
    }

    public function testKnownPolicyKeyRemainsValidWithoutOptionalSafeContext(): void
    {
        self::assertTrue($this->validator()->isManagingFieldAccessReview(
            ManageFieldPermissionVocabulary::FIELD_CONFIGURE,
            'component:managing',
            'permission.revoke',
            [],
        ));
    }

    public function testUnknownManagingPrefixedPermissionIsRejectedWithoutTrustedContext(): void
    {
        self::assertFalse($this->validator()->isManagingFieldAccessReview(
            'managing.field.unknown',
            'component:managing',
            'permission.grant',
            [],
        ));
    }

    public function testWrongScopeOrMutationTypeIsRejected(): void
    {
        self::assertFalse($this->validator()->isManagingFieldAccessReview(
            ManageFieldPermissionVocabulary::FIELD_VIEW,
            'component:cataloging',
            'permission.grant',
            [],
        ));

        self::assertFalse($this->validator()->isManagingFieldAccessReview(
            ManageFieldPermissionVocabulary::FIELD_VIEW,
            'component:managing',
            'permission.replace',
            [],
        ));
    }

    private function validator(): ManageFieldAccessReviewValidator
    {
        return new ManageFieldAccessReviewValidator();
    }
}
