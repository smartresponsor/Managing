<?php

declare(strict_types=1);

namespace App\Managing\Writer\Crud;

use App\Managing\Value\Crud\ManageCrudFieldUserProfileWriteRequest;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileWriteResult;
use App\Managing\WriterInterface\Crud\ManageCrudFieldUserProfileWriterInterface;

/**
 * Safe default writer for installations that have not connected a profile storage backend.
 */
final class ManageCrudNullFieldUserProfileWriter implements ManageCrudFieldUserProfileWriterInterface
{
    public function write(ManageCrudFieldUserProfileWriteRequest $request): ManageCrudFieldUserProfileWriteResult
    {
        return ManageCrudFieldUserProfileWriteResult::rejected('field_user_profile_writer_not_configured');
    }
}
