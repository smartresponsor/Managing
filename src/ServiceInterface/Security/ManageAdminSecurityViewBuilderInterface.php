<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Security;

interface ManageAdminSecurityViewBuilderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function buildSecurityView(): array;
}
