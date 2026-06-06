<?php

declare(strict_types=1);

namespace App\Managing\RepositoryInterface\Crud;

use App\Managing\Value\Crud\ManageCrudFieldUserProfileWriteRequest;

interface ManageCrudFieldViewProfileRuleRepositoryInterface
{
    /** @return array<string, mixed> */
    public function readProfileConfig(?string $subjectIdentifier = null): array;

    /** @return array<string, mixed> */
    public function replacePageRule(ManageCrudFieldUserProfileWriteRequest $request): array;
}
