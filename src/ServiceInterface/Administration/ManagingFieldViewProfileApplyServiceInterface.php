<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Administration;

use App\Managing\Value\Administration\ManagingFieldViewProfileApplyRequest;
use App\Managing\Value\Administration\ManagingFieldViewProfileApplyResult;

interface ManagingFieldViewProfileApplyServiceInterface
{
    public function prepare(ManagingFieldViewProfileApplyRequest $request): ManagingFieldViewProfileApplyResult;
}
