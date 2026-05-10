<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud;

use App\Managing\Entity\ManageProbeRun;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class ManageProbeRunCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ManageProbeRun::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('componentKey');
        yield TextField::new('probeKey');
        yield TextField::new('status');
        yield DateTimeField::new('createdAt')->hideOnForm();
    }
}
