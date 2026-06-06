<?php

declare(strict_types=1);

namespace App\Managing\ValidatorInterface\Administration;

use App\Managing\Value\Administration\ManagingFieldAccessPolicyDescriptor;

interface ManagingFieldAccessPolicyDescriptorValidatorInterface
{
    public function assertValid(ManagingFieldAccessPolicyDescriptor $descriptor): void;
}
