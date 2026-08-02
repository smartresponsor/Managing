<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud\Generated;

use App\Managing\Controller\Crud\AbstractManageContentCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
#[AdminRoute(path: '/cataloging', name: 'cataloging')]
final class CatalogingCrudController extends AbstractManageContentCrudController
{
    public static function getEntityFqcn(): string
    {
        return \App\Cataloging\Entity\Catalog\CatalogCategoryEntity::class;
    }
}
