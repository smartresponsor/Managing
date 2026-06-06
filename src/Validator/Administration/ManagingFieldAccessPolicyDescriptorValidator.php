<?php

declare(strict_types=1);

namespace App\Managing\Validator\Administration;

use App\Managing\ValidatorInterface\Administration\ManagingFieldAccessPolicyDescriptorValidatorInterface;
use App\Managing\Value\Administration\ManagingFieldAccessPolicyDescriptor;
use App\Managing\Value\Administration\ManagingFieldPermissionVocabulary;

final readonly class ManagingFieldAccessPolicyDescriptorValidator implements ManagingFieldAccessPolicyDescriptorValidatorInterface
{
    public function assertValid(ManagingFieldAccessPolicyDescriptor $descriptor): void
    {
        $this->assertFieldAccessPermission($descriptor->permissionKey);
        $this->assertTarget($descriptor);
        $this->assertSubject($descriptor);
        $this->assertEffect($descriptor->effect);
    }

    private function assertFieldAccessPermission(string $permissionKey): void
    {
        if (ManagingFieldPermissionVocabulary::FIELD_VIEW !== trim($permissionKey)) {
            throw new \InvalidArgumentException('Managing field access mutations may only use managing.field.view.');
        }
    }

    private function assertTarget(ManagingFieldAccessPolicyDescriptor $descriptor): void
    {
        $target = $descriptor->target;
        if ('managing' !== strtolower(trim($target->componentKey))) {
            throw new \InvalidArgumentException('Managing field access target must use the Managing component.');
        }

        foreach (['resourceClass' => $target->resourceClass, 'fieldName' => $target->fieldName, 'pageName' => $target->pageName] as $name => $value) {
            if ('' === trim($value)) {
                throw new \InvalidArgumentException(sprintf('Managing field access target %s is required.', $name));
            }
        }

        if ('view' !== trim($target->operation)) {
            throw new \InvalidArgumentException('Managing field access value grants must use the view operation.');
        }
    }

    private function assertSubject(ManagingFieldAccessPolicyDescriptor $descriptor): void
    {
        if (!in_array($descriptor->subjectType, [
            ManagingFieldAccessPolicyDescriptor::SUBJECT_USER,
            ManagingFieldAccessPolicyDescriptor::SUBJECT_ROLE,
            ManagingFieldAccessPolicyDescriptor::SUBJECT_GROUP,
        ], true)) {
            throw new \InvalidArgumentException('Managing field access subject type must be user, role, or group.');
        }

        if ('' === trim($descriptor->subjectIdentifier)) {
            throw new \InvalidArgumentException('Managing field access subject identifier is required.');
        }
    }

    private function assertEffect(string $effect): void
    {
        if (!in_array($effect, [
            ManagingFieldAccessPolicyDescriptor::EFFECT_ALLOW,
            ManagingFieldAccessPolicyDescriptor::EFFECT_DENY,
        ], true)) {
            throw new \InvalidArgumentException('Managing field access effect must be allow or deny.');
        }
    }
}
