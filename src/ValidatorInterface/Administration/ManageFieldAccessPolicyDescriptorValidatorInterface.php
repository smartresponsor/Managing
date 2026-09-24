<?php

declare(strict_types=1);

namespace App\Managing\ValidatorInterface\Administration;

use App\Managing\Value\Administration\ManageFieldAccessPolicyDescriptor;

interface ManageFieldAccessPolicyDescriptorValidatorInterface
{
    public function assertValid(ManageFieldAccessPolicyDescriptor $descriptor): void;
}
