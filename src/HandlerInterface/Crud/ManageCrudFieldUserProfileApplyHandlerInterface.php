<?php

declare(strict_types=1);

namespace App\Managing\HandlerInterface\Crud;

use App\Managing\Value\Crud\ManageCrudFieldUserProfileApplyRequest;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileApplyResult;

interface ManageCrudFieldUserProfileApplyHandlerInterface
{
    public function apply(ManageCrudFieldUserProfileApplyRequest $request): ManageCrudFieldUserProfileApplyResult;
}
