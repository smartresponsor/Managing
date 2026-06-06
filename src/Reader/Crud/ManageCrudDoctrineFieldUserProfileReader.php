<?php

declare(strict_types=1);

namespace App\Managing\Reader\Crud;

use App\Managing\ReaderInterface\Crud\ManageCrudFieldUserProfileReaderInterface;
use App\Managing\RepositoryInterface\Crud\ManageCrudFieldViewProfileRuleRepositoryInterface;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileReadResult;

/**
 * Doctrine/system-storage-backed reader for Managing field view profiles.
 */
final readonly class ManageCrudDoctrineFieldUserProfileReader implements ManageCrudFieldUserProfileReaderInterface
{
    public function __construct(private ManageCrudFieldViewProfileRuleRepositoryInterface $repository)
    {
    }

    public function read(?string $subjectIdentifier = null): ManageCrudFieldUserProfileReadResult
    {
        return ManageCrudFieldUserProfileReadResult::available(
            $this->repository->readProfileConfig($subjectIdentifier),
            'field_user_profile_doctrine_read_available',
        );
    }
}
