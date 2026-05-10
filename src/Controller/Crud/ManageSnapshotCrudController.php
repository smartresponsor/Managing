<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud;

use App\Managing\Entity\ManageSnapshot;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class ManageSnapshotCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ManageSnapshot::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('snapshotKey');
        yield ArrayField::new('payload');
        yield DateTimeField::new('createdAt')->hideOnForm();
    }
}
