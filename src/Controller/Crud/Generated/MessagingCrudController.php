<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud\Generated;

use App\Managing\Controller\Crud\AbstractManageContentCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
#[AdminRoute(path: '/messaging', name: 'messaging')]
final class MessagingCrudController extends AbstractManageContentCrudController
{
    public static function getEntityFqcn(): string
    {
        return \App\Entity\Message\MessageAdminView::class;
    }

    protected static function manageIsReadOnly(): bool
    {
        return true;
    }
}
