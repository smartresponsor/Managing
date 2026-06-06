<?php

declare(strict_types=1);

namespace App\Managing\Reader\Crud;

use App\Managing\ReaderInterface\Crud\ManageCrudFieldUserProfileReaderInterface;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileReadResult;

/**
 * Safe default reader used when no storage backend has been configured.
 */
final readonly class ManageCrudNullFieldUserProfileReader implements ManageCrudFieldUserProfileReaderInterface
{
    public function read(?string $subjectIdentifier = null): ManageCrudFieldUserProfileReadResult
    {
        return ManageCrudFieldUserProfileReadResult::unavailable('field_user_profile_reader_not_configured');
    }
}
