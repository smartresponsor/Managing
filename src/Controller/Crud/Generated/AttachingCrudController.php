<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud\Generated;

use App\Managing\Controller\Crud\AbstractManageContentCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
#[AdminRoute(path: '/attaching', name: 'attaching')]
final class AttachingCrudController extends AbstractManageContentCrudController
{
    public static function getEntityFqcn(): string
    {
        return \App\Attaching\Entity\Attachment\Attachment::class;
    }

    public function index(\EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext $context): \EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore|\Symfony\Component\HttpFoundation\Response
    {
        $doctrine = $this->container->get('doctrine');
        $cacheDir = (string) $this->container->get('parameter_bag')->get('kernel.cache_dir');
        $connection = $doctrine->getConnection();
        $markerFile = $cacheDir.'/attaching_migration_complete.flag';
        (new \App\Managing\Service\Admin\AttachmentIdentifierMigrationService($connection, $markerFile))->migrateIfNeeded();

        return parent::index($context);
    }
}
