<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud;

use App\Managing\Configurator\Crud\ManageCrudActionConfigurator;
use App\Managing\Configurator\Crud\ManageCrudFilterConfigurator;
use App\Managing\Configurator\Crud\ManageCrudPageConfigurator;
use App\Managing\Factory\Crud\ManageCrudFieldFactory;
use App\Managing\Instantiator\Crud\ManageEntityInstantiator;
use App\Managing\Resolver\Crud\ManageCrudEntitySurfaceResolver;
use App\Managing\Workflow\Crud\ManageCrudPublicationWorkflow;
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
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Base CRUD controller for generated Manage content screens.
 *
 * Generated controllers extend this class to reuse EasyAdmin defaults while
 * keeping component-owned controllers constructor-free and deterministic.
 */
abstract class AbstractManageContentCrudController extends AbstractCrudController
{
    use ManageCrudControllerCustomizationTrait;
    private const ACTION_PUBLISH = 'managePublish';
    private const ACTION_UNPUBLISH = 'manageUnpublish';
    private const ACTION_BATCH_PUBLISH = 'manageBatchPublish';
    private const ACTION_BATCH_UNPUBLISH = 'manageBatchUnpublish';

    private ?ManageCrudControllerRuntime $manageCrudRuntime = null;

    #[Required]
    public function setManageCrudActionConfigurator(ManageCrudActionConfigurator $manageCrudActionConfigurator): void
    {
        $this->crudRuntime()->setActionConfigurator($manageCrudActionConfigurator);
    }

    #[Required]
    public function setManageCrudEntitySurfaceResolver(ManageCrudEntitySurfaceResolver $manageCrudEntitySurfaceResolver): void
    {
        $this->crudRuntime()->setEntitySurfaceResolver($manageCrudEntitySurfaceResolver);
    }

    #[Required]
    public function setManageCrudFieldFactory(ManageCrudFieldFactory $manageCrudFieldFactory): void
    {
        $this->crudRuntime()->setFieldFactory($manageCrudFieldFactory);
    }

    #[Required]
    public function setManageCrudFilterConfigurator(ManageCrudFilterConfigurator $manageCrudFilterConfigurator): void
    {
        $this->crudRuntime()->setFilterConfigurator($manageCrudFilterConfigurator);
    }

    #[Required]
    public function setManageCrudPageConfigurator(ManageCrudPageConfigurator $manageCrudPageConfigurator): void
    {
        $this->crudRuntime()->setPageConfigurator($manageCrudPageConfigurator);
    }

    #[Required]
    public function setManageCrudPublicationWorkflow(ManageCrudPublicationWorkflow $manageCrudPublicationWorkflow): void
    {
        $this->crudRuntime()->setPublicationWorkflow($manageCrudPublicationWorkflow);
    }

    #[Required]
    public function setManageEntityInstantiator(ManageEntityInstantiator $manageEntityInstantiator): void
    {
        $this->crudRuntime()->setEntityInstantiator($manageEntityInstantiator);
    }

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

    /** @return list<string> */
    private function searchFields(): array
    {
        return $this->entitySurfaceResolver()->searchFields(static::getEntityFqcn(), static::manageSearchFieldCandidates());
    }

    /** @return list<string> */
    private function statusFields(): array
    {
        return $this->entitySurfaceResolver()->statusFields(static::getEntityFqcn(), static::manageStatusFieldCandidates());
    }

    /** @return list<string> */
    private function publicationFlagFields(): array
    {
        return $this->entitySurfaceResolver()->publicationFlagFields(static::getEntityFqcn(), static::managePublicationFlagCandidates());
    }

    /** @return list<string> */
    private function filterDateFields(): array
    {
        return $this->entitySurfaceResolver()->filterDateFields(static::getEntityFqcn(), static::managePublicationDateCandidates());
    }

    /** @return list<string> */
    private function statusFieldCandidates(): array
    {
        return $this->entitySurfaceResolver()->statusFieldCandidates(static::manageStatusFieldCandidates());
    }

    /** @return list<string> */
    private function publicationFlagCandidates(): array
    {
        return $this->entitySurfaceResolver()->publicationFlagFieldCandidates(static::managePublicationFlagCandidates());
    }

    /** @return list<string> */
    private function publicationDateCandidates(): array
    {
        return $this->entitySurfaceResolver()->publicationDateFieldCandidates(static::managePublicationDateCandidates());
    }

    private function supportsPublication(): bool
    {
        return $this->publicationWorkflow()->supports(static::getEntityFqcn(), $this->publicationFlagCandidates(), $this->publicationDateCandidates());
    }

    private function canPublishEntity(object $entity): bool
    {
        return $this->publicationWorkflow()->canPublish(static::getEntityFqcn(), $entity, $this->publicationFlagCandidates(), $this->publicationDateCandidates());
    }

    private function canUnpublishEntity(object $entity): bool
    {
        return $this->publicationWorkflow()->canUnpublish(static::getEntityFqcn(), $entity, $this->publicationFlagCandidates(), $this->publicationDateCandidates());
    }

    private function redirectBack(?string $referer): RedirectResponse
    {
        return $this->redirect($referer ?: '/manage');
    }

    private function actionConfigurator(): ManageCrudActionConfigurator
    {
        return $this->crudRuntime()->actionConfigurator();
    }

    private function entitySurfaceResolver(): ManageCrudEntitySurfaceResolver
    {
        return $this->crudRuntime()->entitySurfaceResolver();
    }

    private function fieldFactory(): ManageCrudFieldFactory
    {
        return $this->crudRuntime()->fieldFactory();
    }

    private function filterConfigurator(): ManageCrudFilterConfigurator
    {
        return $this->crudRuntime()->filterConfigurator();
    }

    private function pageConfigurator(): ManageCrudPageConfigurator
    {
        return $this->crudRuntime()->pageConfigurator();
    }

    private function publicationWorkflow(): ManageCrudPublicationWorkflow
    {
        return $this->crudRuntime()->publicationWorkflow();
    }

    private function entityInstantiator(): ManageEntityInstantiator
    {
        return $this->crudRuntime()->entityInstantiator();
    }

    private function crudRuntime(): ManageCrudControllerRuntime
    {
        return $this->manageCrudRuntime ??= new ManageCrudControllerRuntime();
    }
}
