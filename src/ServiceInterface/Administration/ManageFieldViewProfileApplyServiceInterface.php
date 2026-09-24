<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Administration;

use App\Managing\Value\Administration\ManageFieldViewProfileApplyRequest;
use App\Managing\Value\Administration\ManageFieldViewProfileApplyResult;

interface ManageFieldViewProfileApplyServiceInterface
{
    public function prepare(ManageFieldViewProfileApplyRequest $request): ManageFieldViewProfileApplyResult;
}
