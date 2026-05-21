<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base CRUD controller for Manage content screens.
 *
 * Component CRUD controllers extend this class when they want the common
 * CMS-like EasyAdmin defaults: business-first index fields, content actions,
 * publication actions, search, filters, pagination and batch operations.
 *
 * The controller is intentionally thin. Reflection, field creation,
 * publication-state mutation and constructor-heavy entity instantiation are
 * delegated to Symfony-oriented CRUD services so generated controllers remain
 * small without turning this base class into the full implementation surface.
 */
abstract class AbstractManageContentCrudController extends AbstractCrudController
{
    use ManageCrudControllerCustomizationTrait;
    use ManageCrudControllerRuntimeInjectionTrait;
    use ManageCrudControllerSurfaceTrait;
    private const ACTION_PUBLISH = 'managePublish';
    private const ACTION_UNPUBLISH = 'manageUnpublish';
    private const ACTION_BATCH_PUBLISH = 'manageBatchPublish';
    private const ACTION_BATCH_UNPUBLISH = 'manageBatchUnpublish';

    public function configureCrud(Crud $crud): Crud
    {
        $labels = $this->entitySurfaceResolver()->labels(
            static::getEntityFqcn(),
            static::manageContentSingularLabel(),
            static::manageContentPluralLabel(),
        );

        return $this->pageConfigurator()->configure(
            $crud,
            $labels['singular'],
            $labels['plural'],
            $this->searchFields(),
            $this->entitySurfaceResolver()->defaultSort(static::getEntityFqcn()),
            static::manageIsReadOnly(),
        );
    }

    public function configureActions(Actions $actions): Actions
    {
        return $this->actionConfigurator()->configure(
            $actions,
            static::manageIsReadOnly(),
            $this->supportsPublication(),
            self::ACTION_PUBLISH,
            self::ACTION_UNPUBLISH,
            self::ACTION_BATCH_PUBLISH,
            self::ACTION_BATCH_UNPUBLISH,
            fn (object $entity): bool => $this->canPublishEntity($entity),
            fn (object $entity): bool => $this->canUnpublishEntity($entity),
        );
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $this->filterConfigurator()->configure(
            $filters,
            $this->statusFields(),
            $this->publicationFlagFields(),
            $this->filterDateFields(),
        );
    }

    public function configureFields(string $pageName): iterable
    {
        return $this->fieldFactory()->fields(
            static::getEntityFqcn(),
            $pageName,
            $this->statusFieldCandidates(),
            $this->publicationFlagCandidates(),
            $this->publicationDateCandidates(),
            static::manageArrayChoiceFields(),
            static::manageFieldTypeOverrides(),
        );
    }

    public function publish(AdminContext $context, EntityManagerInterface $entityManager): Response
    {
        $this->publicationWorkflow()->setCurrentEntityPublicationState(
            $context,
            $entityManager,
            static::getEntityFqcn(),
            true,
            $this->publicationFlagCandidates(),
            $this->publicationDateCandidates(),
        );

        return $this->redirectBack($context->getRequest()->headers->get('referer'));
    }

    public function unpublish(AdminContext $context, EntityManagerInterface $entityManager): Response
    {
        $this->publicationWorkflow()->setCurrentEntityPublicationState(
            $context,
            $entityManager,
            static::getEntityFqcn(),
            false,
            $this->publicationFlagCandidates(),
            $this->publicationDateCandidates(),
        );

        return $this->redirectBack($context->getRequest()->headers->get('referer'));
    }

    public function batchPublish(BatchActionDto $batchActionDto, EntityManagerInterface $entityManager, RequestStack $requestStack): Response
    {
        $this->publicationWorkflow()->setBatchPublicationState(
            $batchActionDto,
            $entityManager,
            true,
            $this->publicationFlagCandidates(),
            $this->publicationDateCandidates(),
        );

        return $this->redirectBack($requestStack->getCurrentRequest()?->headers->get('referer'));
    }

    public function batchUnpublish(BatchActionDto $batchActionDto, EntityManagerInterface $entityManager, RequestStack $requestStack): Response
    {
        $this->publicationWorkflow()->setBatchPublicationState(
            $batchActionDto,
            $entityManager,
            false,
            $this->publicationFlagCandidates(),
            $this->publicationDateCandidates(),
        );

        return $this->redirectBack($requestStack->getCurrentRequest()?->headers->get('referer'));
    }

    public function createEntity(string $entityFqcn): object
    {
        return $this->entityInstantiator()->instantiate($entityFqcn);
    }

    private function redirectBack(?string $referer): RedirectResponse
    {
        return $this->redirect($referer ?: '/manage');
    }
}
