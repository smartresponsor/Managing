<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Administration;

use App\Managing\Value\Administration\ManageFieldAccessMutationReviewInput;
use App\Managing\Value\Administration\ManageFieldAccessMutationReviewResult;

interface ManageFieldAccessMutationReviewServiceInterface
{
    public function review(ManageFieldAccessMutationReviewInput $input): ManageFieldAccessMutationReviewResult;
}
