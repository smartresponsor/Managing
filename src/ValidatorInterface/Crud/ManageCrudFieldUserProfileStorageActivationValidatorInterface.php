<?php

declare(strict_types=1);

namespace App\Managing\ValidatorInterface\Crud;

use App\Managing\Value\Crud\ManageCrudFieldUserProfileStorageActivationReport;

interface ManageCrudFieldUserProfileStorageActivationValidatorInterface
{
    public function validate(): ManageCrudFieldUserProfileStorageActivationReport;
}
