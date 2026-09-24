<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Administration;

use App\Managing\Validator\Administration\ManageFieldAccessPolicyDescriptorValidator;
use App\Managing\Value\Administration\ManageFieldAccessPolicyDescriptor;
use App\Managing\Value\Administration\ManageFieldAccessTarget;
use App\Managing\Value\Administration\ManageFieldPermissionVocabulary;
use PHPUnit\Framework\TestCase;

final class ManageFieldAccessPolicyDescriptorValidatorTest extends TestCase
{
    public function testValidManagingFieldViewDescriptorPasses(): void
    {
        $validator = new ManageFieldAccessPolicyDescriptorValidator();
        $validator->assertValid($this->descriptor(ManageFieldPermissionVocabulary::FIELD_VIEW));

        self::addToAssertionCount(1);
    }

    public function testProfilePermissionCannotBeUsedAsFieldValueAccessGrant(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('managing.field.view');

        $this->validator()->assertValid(
            $this->descriptor(ManageFieldPermissionVocabulary::PROFILE_SELF_UPDATE),
        );
    }

    public function testNonManagingComponentIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Managing component');

        $this->validator()->assertValid(new ManageFieldAccessPolicyDescriptor(
            new ManageFieldAccessTarget('Cataloging', 'App\\Cataloging\\Entity\\Product', 'internalCost', 'detail'),
            ManageFieldPermissionVocabulary::FIELD_VIEW,
            ManageFieldAccessPolicyDescriptor::SUBJECT_ROLE,
            'catalog.manager',
            ManageFieldAccessPolicyDescriptor::EFFECT_ALLOW,
        ));
    }

    private function descriptor(string $permissionKey): ManageFieldAccessPolicyDescriptor
    {
        return new ManageFieldAccessPolicyDescriptor(
            new ManageFieldAccessTarget(
                'Managing',
                'App\\Cataloging\\Entity\\Catalog\\CatalogCategoryEntity',
                'internalCost',
                'detail',
            ),
            $permissionKey,
            ManageFieldAccessPolicyDescriptor::SUBJECT_ROLE,
            'catalog.manager',
            ManageFieldAccessPolicyDescriptor::EFFECT_ALLOW,
        );
    }

    private function validator(): ManageFieldAccessPolicyDescriptorValidator
    {
        return new ManageFieldAccessPolicyDescriptorValidator();
    }
}
