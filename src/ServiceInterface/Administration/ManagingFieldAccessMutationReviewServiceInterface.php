<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Administration;

use App\Managing\Value\Administration\ManagingFieldAccessMutationReviewInput;
use App\Managing\Value\Administration\ManagingFieldAccessMutationReviewResult;

interface ManagingFieldAccessMutationReviewServiceInterface
{
    public function review(ManagingFieldAccessMutationReviewInput $input): ManagingFieldAccessMutationReviewResult;
}
