<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud\Generated;

use App\Managing\Controller\Crud\AbstractManageContentCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
#[AdminRoute(path: '/localizing', name: 'localizing')]
final class LocalizingCrudController extends AbstractManageContentCrudController
{
    public static function getEntityFqcn(): string
    {
        return \App\Localizing\Entity\TranslationMessage::class;
    }
}
