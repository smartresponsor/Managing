<?php

declare(strict_types=1);

namespace App\Managing\InspectorInterface\Crud;

use App\Managing\Value\Crud\ManageCrudFieldVisibilityInspectionReport;
use App\Managing\Value\Crud\ManageCrudFieldVisibilityInspectionRequest;

interface ManageCrudFieldVisibilityInspectorInterface
{
    public function inspect(ManageCrudFieldVisibilityInspectionRequest $request): ManageCrudFieldVisibilityInspectionReport;
}
