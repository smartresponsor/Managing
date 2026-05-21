<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

/**
 * Applies the reusable EasyAdmin page-level defaults for Manage CRUD screens.
 */
final class ManageCrudPageConfigurator
{
    /**
     * @param list<string>          $searchFields
     * @param array<string, string> $defaultSort
     */
    public function configure(
        Crud $crud,
        string $singularLabel,
        string $pluralLabel,
        array $searchFields,
        array $defaultSort,
        bool $readOnly,
    ): Crud {
        return $crud
            ->setEntityLabelInSingular($singularLabel)
            ->setEntityLabelInPlural($pluralLabel)
            ->setPageTitle(Crud::PAGE_INDEX, $pluralLabel)
            ->setPageTitle(Crud::PAGE_NEW, 'Create '.$singularLabel)
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit '.$singularLabel)
            ->setPageTitle(Crud::PAGE_DETAIL, $singularLabel.' details')
            ->setSearchFields($searchFields)
            ->setPaginatorPageSize(30)
            ->setPaginatorRangeSize(4)
            ->setDefaultSort($defaultSort)
            ->showEntityActionsInlined()
            ->askConfirmationOnBatchActions()
            ->setDefaultRowAction($readOnly ? Action::DETAIL : Action::EDIT);
    }
}
