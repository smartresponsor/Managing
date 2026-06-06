<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Administration;

use App\Managing\Value\Administration\ManagingFieldAccessMutationApplyResult;

interface ManagingFieldAccessMutationApplyServiceInterface
{
    public function applyReviewedFieldAccessMutation(string $requestKey, string $requestedBySubject): ManagingFieldAccessMutationApplyResult;
}
