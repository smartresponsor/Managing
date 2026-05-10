<?php

declare(strict_types=1);

namespace App\Managing\Service\Security;

use App\Managing\ServiceInterface\Security\ManageAdminAccessPolicyInterface;
use App\Managing\ServiceInterface\Security\ManageAdminSecurityViewBuilderInterface;

final readonly class ManageAdminSecurityViewBuilder implements ManageAdminSecurityViewBuilderInterface
{
    public function __construct(private ManageAdminAccessPolicyInterface $accessPolicy)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSecurityView(): array
    {
        $policy = $this->accessPolicy->describe();
        $suggestedAccessControl = sprintf('^%s', preg_quote($policy['route_prefix'], '#'));

        return [
            'policy' => $policy,
            'suggested_access_control' => $suggestedAccessControl,
            'suggested_role' => $policy['required_role'],
        ];
    }
}
