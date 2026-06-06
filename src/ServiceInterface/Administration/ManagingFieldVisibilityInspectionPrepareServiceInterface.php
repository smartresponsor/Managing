<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Administration;

use App\Managing\Value\Administration\ManagingFieldVisibilityInspectionPrepareRequest;
use App\Managing\Value\Administration\ManagingFieldVisibilityInspectionPrepareResult;

interface ManagingFieldVisibilityInspectionPrepareServiceInterface
{
    public function prepare(ManagingFieldVisibilityInspectionPrepareRequest $request): ManagingFieldVisibilityInspectionPrepareResult;
}
