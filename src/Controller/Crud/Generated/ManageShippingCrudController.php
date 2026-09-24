<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud\Generated;

use App\Managing\Controller\Crud\ManageContentCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
#[AdminRoute(path: '/shipping', name: 'shipping')]
final class ManageShippingCrudController extends ManageContentCrudController
{
    public static function getEntityFqcn(): string
    {
        return \App\Shipping\Entity\Shipping\ShipmentEntity::class;
    }

}
