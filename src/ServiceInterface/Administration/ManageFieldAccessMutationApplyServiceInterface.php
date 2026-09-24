<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Administration;

use App\Managing\Value\Administration\ManageFieldAccessMutationApplyResult;

interface ManageFieldAccessMutationApplyServiceInterface
{
    public function applyReviewedFieldAccessMutation(string $requestKey, string $requestedBySubject): ManageFieldAccessMutationApplyResult;
}
