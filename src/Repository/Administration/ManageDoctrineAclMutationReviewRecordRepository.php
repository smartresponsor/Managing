<?php

declare(strict_types=1);

namespace App\Managing\Repository\Administration;

use App\Administering\Entity\AdministrationAclMutationReviewRecord;
use App\Managing\RepositoryInterface\Administration\ManageAclMutationReviewRecordRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

final readonly class ManageDoctrineAclMutationReviewRecordRepository implements ManageAclMutationReviewRecordRepositoryInterface
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    public function findByRequestKey(string $requestKey): ?AdministrationAclMutationReviewRecord
    {
        $manager = $this->managerRegistry->getManagerForClass(AdministrationAclMutationReviewRecord::class);
        if (null === $manager) {
            throw new \LogicException('No Doctrine manager is configured for Administering ACL mutation review records.');
        }

        $record = $manager->getRepository(AdministrationAclMutationReviewRecord::class)->findOneBy(['requestKey' => $requestKey]);

        return $record instanceof AdministrationAclMutationReviewRecord ? $record : null;
    }
}
