<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Administration;

use App\Managing\Value\Administration\ManageFieldVisibilityInspectionPrepareRequest;
use App\Managing\Value\Administration\ManageFieldVisibilityInspectionPrepareResult;

interface ManageFieldVisibilityInspectionPrepareServiceInterface
{
    public function prepare(ManageFieldVisibilityInspectionPrepareRequest $request): ManageFieldVisibilityInspectionPrepareResult;
}
