<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud\Generated;

use App\Managing\Controller\Crud\AbstractManageContentCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
#[AdminRoute(path: '/applicating', name: 'applicating')]
final class ApplicatingCrudController extends AbstractManageContentCrudController
{
    public static function getEntityFqcn(): string
    {
        return \App\Applicating\Entity\ApplicationUser::class;
    }

    /** @return array<string, array<string, string>> */
    protected static function manageArrayChoiceFields(): array
    {
        return [
            'roles' => [
                'Application admin' => 'ROLE_APPLICATION_ADMIN',
                'Application manager' => 'ROLE_APPLICATION_MANAGER',
                'Application viewer' => 'ROLE_APPLICATION_VIEWER',
                'Application user' => 'ROLE_APPLICATION_USER',
            ],
        ];
    }
}
