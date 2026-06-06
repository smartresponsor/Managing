<?php

declare(strict_types=1);

namespace App\Managing\ReaderInterface\Crud;

use App\Managing\Value\Crud\ManageCrudFieldUserProfileReadResult;

interface ManageCrudFieldUserProfileReaderInterface
{
    public function read(?string $subjectIdentifier = null): ManageCrudFieldUserProfileReadResult;
}
