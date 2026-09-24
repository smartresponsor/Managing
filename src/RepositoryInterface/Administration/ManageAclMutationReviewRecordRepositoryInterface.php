<?php

declare(strict_types=1);

namespace App\Managing\RepositoryInterface\Administration;

use App\Administering\Entity\AdministrationAclMutationReviewRecord;

interface ManageAclMutationReviewRecordRepositoryInterface
{
    public function findByRequestKey(string $requestKey): ?AdministrationAclMutationReviewRecord;
}
