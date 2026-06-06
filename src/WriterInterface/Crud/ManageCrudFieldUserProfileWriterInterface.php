<?php

declare(strict_types=1);

namespace App\Managing\WriterInterface\Crud;

use App\Managing\Value\Crud\ManageCrudFieldUserProfileWriteRequest;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileWriteResult;

interface ManageCrudFieldUserProfileWriterInterface
{
    public function write(ManageCrudFieldUserProfileWriteRequest $request): ManageCrudFieldUserProfileWriteResult;
}
