<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud\Generated;

use App\Managing\Controller\Crud\AbstractManageContentCrudController;
use App\Managing\Controller\Crud\ManageAttachmentIdentifierMigrationTrait;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
#[AdminRoute(path: '/attaching', name: 'attaching')]
final class AttachingCrudController extends AbstractManageContentCrudController
{
    use ManageAttachmentIdentifierMigrationTrait;

    public static function getEntityFqcn(): string
    {
        return \App\Attaching\Entity\Attachment\Attachment::class;
    }

    public function index(\EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext $context): \EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore|\Symfony\Component\HttpFoundation\Response
    {
        $this->migrateAttachmentIdentifierIfNeeded();

        return parent::index($context);
    }
}
