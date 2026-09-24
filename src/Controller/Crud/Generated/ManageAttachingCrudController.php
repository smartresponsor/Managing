<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud\Generated;

use App\Managing\Controller\Crud\ManageContentCrudController;
use App\Managing\Trait\Crud\ManageAttachmentIdentifierMigrationTrait;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
#[AdminRoute(path: '/attaching', name: 'attaching')]
final class ManageAttachingCrudController extends ManageContentCrudController
{
    use ManageAttachmentIdentifierMigrationTrait;

    public static function getEntityFqcn(): string
    {
        return \App\Attaching\Entity\Attachment\AttachmentEntity::class;
    }

    public function index(\EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext $context): \EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore|\Symfony\Component\HttpFoundation\Response
    {
        $this->migrateAttachmentIdentifierIfNeeded();

        return parent::index($context);
    }
}
