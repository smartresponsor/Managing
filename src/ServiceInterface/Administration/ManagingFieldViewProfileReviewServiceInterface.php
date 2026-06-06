<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Administration;

use App\Managing\Value\Administration\ManagingFieldViewProfileEditRequest;
use App\Managing\Value\Administration\ManagingFieldViewProfileReviewResult;

interface ManagingFieldViewProfileReviewServiceInterface
{
    public function review(ManagingFieldViewProfileEditRequest $request): ManagingFieldViewProfileReviewResult;
}
