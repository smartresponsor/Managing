<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

/**
 * Builds the shared EasyAdmin action surface for generated Manage CRUD screens.
 *
 * The controller owns the route methods; this service owns the reusable action
 * vocabulary, labels, icons and publication batch-action wiring.
 */
final class ManageCrudActionConfigurator
{
    /**
     * @param callable(object): bool $canPublish
     * @param callable(object): bool $canUnpublish
     */
    public function configure(
        Actions $actions,
        bool $readOnly,
        bool $supportsPublication,
        string $publishActionName,
        string $unpublishActionName,
        string $batchPublishActionName,
        string $batchUnpublishActionName,
        callable $canPublish,
        callable $canUnpublish,
    ): Actions {
        if ($readOnly) {
            return $actions
                ->add(Crud::PAGE_INDEX, Action::DETAIL)
                ->disable(Action::NEW, Action::EDIT, Action::DELETE);
        }

        $publish = Action::new($publishActionName, 'Publish', 'fa fa-eye')
            ->linkToCrudAction('publish')
            ->asSuccessAction()
            ->displayIf(static fn (object $entity): bool => (bool) $canPublish($entity));

        $unpublish = Action::new($unpublishActionName, 'Unpublish', 'fa fa-eye-slash')
            ->linkToCrudAction('unpublish')
            ->asWarningAction()
            ->displayIf(static fn (object $entity): bool => (bool) $canUnpublish($entity));

        $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $publish)
            ->add(Crud::PAGE_DETAIL, $publish)
            ->add(Crud::PAGE_EDIT, $publish)
            ->add(Crud::PAGE_INDEX, $unpublish)
            ->add(Crud::PAGE_DETAIL, $unpublish)
            ->add(Crud::PAGE_EDIT, $unpublish)
            ->update(Crud::PAGE_INDEX, Action::NEW, static fn (Action $action): Action => $action->setLabel('Create new')->setIcon('fa fa-plus')->asPrimaryAction())
            ->update(Crud::PAGE_INDEX, Action::EDIT, static fn (Action $action): Action => $action->setLabel('Edit'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action): Action => $action->setLabel('Delete')->asDangerAction()->askConfirmation())
            ->update(Crud::PAGE_DETAIL, Action::DELETE, static fn (Action $action): Action => $action->asDangerAction()->askConfirmation());

        if (!$supportsPublication) {
            return $actions;
        }

        return $actions
            ->addBatchAction(
                Action::new($batchPublishActionName, 'Publish selected', 'fa fa-eye')
                    ->linkToCrudAction('batchPublish')
                    ->createAsBatchAction()
                    ->asSuccessAction()
            )
            ->addBatchAction(
                Action::new($batchUnpublishActionName, 'Unpublish selected', 'fa fa-eye-slash')
                    ->linkToCrudAction('batchUnpublish')
                    ->createAsBatchAction()
                    ->asWarningAction()
            );
    }
}
