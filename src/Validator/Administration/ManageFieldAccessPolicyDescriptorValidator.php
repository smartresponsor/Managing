<?php

declare(strict_types=1);

namespace App\Managing\Validator\Administration;

use App\Managing\ValidatorInterface\Administration\ManageFieldAccessPolicyDescriptorValidatorInterface;
use App\Managing\Value\Administration\ManageFieldAccessPolicyDescriptor;
use App\Managing\Value\Administration\ManageFieldPermissionVocabulary;

final readonly class ManageFieldAccessPolicyDescriptorValidator implements ManageFieldAccessPolicyDescriptorValidatorInterface
{
    public function assertValid(ManageFieldAccessPolicyDescriptor $descriptor): void
    {
        $this->assertFieldAccessPermission($descriptor->permissionKey);
        $this->assertTarget($descriptor);
        $this->assertSubject($descriptor);
        $this->assertEffect($descriptor->effect);
    }

    private function assertFieldAccessPermission(string $permissionKey): void
    {
        if (ManageFieldPermissionVocabulary::FIELD_VIEW !== trim($permissionKey)) {
            throw new \InvalidArgumentException('Managing field access mutations may only use managing.field.view.');
        }
    }

    private function assertTarget(ManageFieldAccessPolicyDescriptor $descriptor): void
    {
        $target = $descriptor->target;
        if ('managing' !== strtolower(trim($target->componentKey))) {
            throw new \InvalidArgumentException('Managing field access target must use the Managing component.');
        }

        foreach (['resourceClass' => $target->resourceClass, 'fieldName' => $target->fieldName, 'pageName' => $target->pageName] as $nameEntity => $value) {
            if ('' === trim($value)) {
                throw new \InvalidArgumentException(sprintf('Managing field access target %s is required.', $nameEntity));
            }
        }

        if ('view' !== trim($target->operation)) {
            throw new \InvalidArgumentException('Managing field access value grants must use the view operation.');
        }
    }

    private function assertSubject(ManageFieldAccessPolicyDescriptor $descriptor): void
    {
        if (!in_array($descriptor->subjectType, [
            ManageFieldAccessPolicyDescriptor::SUBJECT_USER,
            ManageFieldAccessPolicyDescriptor::SUBJECT_ROLE,
            ManageFieldAccessPolicyDescriptor::SUBJECT_GROUP,
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
            ManageFieldAccessPolicyDescriptor::EFFECT_ALLOW,
            ManageFieldAccessPolicyDescriptor::EFFECT_DENY,
        ], true)) {
            throw new \InvalidArgumentException('Managing field access effect must be allow or deny.');
        }
    }
}
