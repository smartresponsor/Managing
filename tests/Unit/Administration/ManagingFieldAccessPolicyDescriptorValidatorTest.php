<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Administration;

use App\Managing\Validator\Administration\ManagingFieldAccessPolicyDescriptorValidator;
use App\Managing\Value\Administration\ManagingFieldAccessPolicyDescriptor;
use App\Managing\Value\Administration\ManagingFieldAccessTarget;
use App\Managing\Value\Administration\ManagingFieldPermissionVocabulary;
use PHPUnit\Framework\TestCase;

final class ManagingFieldAccessPolicyDescriptorValidatorTest extends TestCase
{
    public function testValidManagingFieldViewDescriptorPasses(): void
    {
        $validator = new ManagingFieldAccessPolicyDescriptorValidator();
        $validator->assertValid($this->descriptor(ManagingFieldPermissionVocabulary::FIELD_VIEW));

        self::addToAssertionCount(1);
    }

    public function testProfilePermissionCannotBeUsedAsFieldValueAccessGrant(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('managing.field.view');

        $this->validator()->assertValid(
            $this->descriptor(ManagingFieldPermissionVocabulary::PROFILE_SELF_UPDATE),
        );
    }

    public function testNonManagingComponentIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Managing component');

        $this->validator()->assertValid(new ManagingFieldAccessPolicyDescriptor(
            new ManagingFieldAccessTarget('Cataloging', 'App\\Cataloging\\Entity\\Product', 'internalCost', 'detail'),
            ManagingFieldPermissionVocabulary::FIELD_VIEW,
            ManagingFieldAccessPolicyDescriptor::SUBJECT_ROLE,
            'catalog.manager',
            ManagingFieldAccessPolicyDescriptor::EFFECT_ALLOW,
        ));
    }

    private function descriptor(string $permissionKey): ManagingFieldAccessPolicyDescriptor
    {
        return new ManagingFieldAccessPolicyDescriptor(
            new ManagingFieldAccessTarget(
                'Managing',
                'App\\Cataloging\\Entity\\Catalog\\CatalogCategoryEntity',
                'internalCost',
                'detail',
            ),
            $permissionKey,
            ManagingFieldAccessPolicyDescriptor::SUBJECT_ROLE,
            'catalog.manager',
            ManagingFieldAccessPolicyDescriptor::EFFECT_ALLOW,
        );
    }

    private function validator(): ManagingFieldAccessPolicyDescriptorValidator
    {
        return new ManagingFieldAccessPolicyDescriptorValidator();
    }
}
