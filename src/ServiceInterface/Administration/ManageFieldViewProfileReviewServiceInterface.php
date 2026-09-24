<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Administration;

use App\Managing\Value\Administration\ManageFieldViewProfileEditRequest;
use App\Managing\Value\Administration\ManageFieldViewProfileReviewResult;

interface ManageFieldViewProfileReviewServiceInterface
{
    public function review(ManageFieldViewProfileEditRequest $request): ManageFieldViewProfileReviewResult;
}
