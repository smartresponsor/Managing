<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base CRUD controller for Manage content screens.
 *
 * Component CRUD controllers can extend this class when they want the common
 * CMS-like EasyAdmin defaults: business-first index fields, content actions,
 * publication actions, search, filters, pagination and batch operations.
 *
 * The class intentionally does not expose Manage/system metadata such as
 * componentKey, resourceKey or entityClass on content index pages. Those values
 * belong to diagnostics/readiness surfaces, not to content-manager screens.
 */
abstract class AbstractManageContentCrudController extends AbstractCrudController
{
    private const ACTION_PUBLISH = 'managePublish';
    private const ACTION_UNPUBLISH = 'manageUnpublish';
    private const ACTION_BATCH_PUBLISH = 'manageBatchPublish';
    private const ACTION_BATCH_UNPUBLISH = 'manageBatchUnpublish';

    /**
     * Override this in a concrete component CRUD controller when a domain needs
     * a custom plural label in the EasyAdmin header.
     */
    protected static function manageContentPluralLabel(): ?string
    {
        return null;
    }

    /**
     * Override this in a concrete component CRUD controller when a domain needs
     * a custom singular label in EasyAdmin breadcrumbs/actions.
     */
    protected static function manageContentSingularLabel(): ?string
    {
        return null;
    }

    /**
     * Override this in a concrete component CRUD controller to pin domain fields
     * that must be searched. The base class will keep only fields that exist on
     * the actual entity.
     *
     * @return list<string>
     */
    protected static function manageSearchFieldCandidates(): array
    {
        return [
            'id',
            'firstTitle',
            'title',
            'name',
            'label',
            'code',
            'slug',
            'sku',
            'status',
            'state',
        ];
    }

    /**
     * @return list<string>
     */
    protected static function manageStatusFieldCandidates(): array
    {
        return ['status', 'state'];
    }

    /**
     * @return list<string>
     */
    protected static function managePublicationFlagCandidates(): array
    {
        return ['published', 'isPublished', 'enabled', 'active'];
    }

    /**
     * @return list<string>
     */
    protected static function managePublicationDateCandidates(): array
    {
        return ['publishedAt', 'publishAt'];
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud
            ->setEntityLabelInSingular(static::manageContentSingularLabel() ?? $this->humanizeEntityShortName(false))
            ->setEntityLabelInPlural(static::manageContentPluralLabel() ?? $this->humanizeEntityShortName(true))
            ->setPageTitle(Crud::PAGE_INDEX, static::manageContentPluralLabel() ?? $this->humanizeEntityShortName(true))
            ->setPageTitle(Crud::PAGE_NEW, 'Create '.$this->humanizeEntityShortName(false))
            ->setPageTitle(Crud::PAGE_EDIT, 'Edit '.$this->humanizeEntityShortName(false))
            ->setPageTitle(Crud::PAGE_DETAIL, $this->humanizeEntityShortName(false).' details')
            ->setSearchFields($this->existingFields(static::manageSearchFieldCandidates()))
            ->setPaginatorPageSize(30)
            ->setPaginatorRangeSize(4)
            ->setDefaultSort($this->defaultSort())
            ->showEntityActionsInlined()
            ->askConfirmationOnBatchActions()
            ->setDefaultRowAction(Action::EDIT);

        return $crud;
    }

    public function configureActions(Actions $actions): Actions
    {
        $publish = Action::new(self::ACTION_PUBLISH, 'Publish', 'fa fa-eye')
            ->linkToCrudAction('publish')
            ->asSuccessAction()
            ->displayIf(fn (object $entity): bool => $this->canPublishEntity($entity));

        $unpublish = Action::new(self::ACTION_UNPUBLISH, 'Unpublish', 'fa fa-eye-slash')
            ->linkToCrudAction('unpublish')
            ->asWarningAction()
            ->displayIf(fn (object $entity): bool => $this->canUnpublishEntity($entity));

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

        if ($this->supportsPublication()) {
            $actions
                ->addBatchAction(
                    Action::new(self::ACTION_BATCH_PUBLISH, 'Publish selected', 'fa fa-eye')
                        ->linkToCrudAction('batchPublish')
                        ->createAsBatchAction()
                        ->asSuccessAction()
                )
                ->addBatchAction(
                    Action::new(self::ACTION_BATCH_UNPUBLISH, 'Unpublish selected', 'fa fa-eye-slash')
                        ->linkToCrudAction('batchUnpublish')
                        ->createAsBatchAction()
                        ->asWarningAction()
                );
        }

        return $actions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        foreach ($this->existingFields(static::manageStatusFieldCandidates()) as $field) {
            $filters->add(TextFilter::new($field));
        }

        foreach ($this->existingFields(static::managePublicationFlagCandidates()) as $field) {
            $filters->add(BooleanFilter::new($field));
        }

        foreach ($this->existingFields(['createdAt', 'updatedAt', ...static::managePublicationDateCandidates()]) as $field) {
            $filters->add(DateTimeFilter::new($field));
        }

        return $filters;
    }

    public function configureFields(string $pageName): iterable
    {
        if ($this->hasField('id')) {
            yield IdField::new('id', 'ID')->onlyOnIndex();
        }

        foreach (['firstTitle', 'title', 'name', 'label'] as $field) {
            if ($this->hasField($field)) {
                yield TextField::new($field, $this->labelFor($field))->setMaxLength(96);
                break;
            }
        }

        foreach (['code', 'slug', 'sku'] as $field) {
            if ($this->hasField($field)) {
                yield TextField::new($field, $this->labelFor($field))->setMaxLength(64)->hideOnForm();
            }
        }

        foreach (static::manageStatusFieldCandidates() as $field) {
            if ($this->hasField($field)) {
                yield TextField::new($field, $this->labelFor($field))->setMaxLength(32);
                break;
            }
        }

        foreach (static::managePublicationFlagCandidates() as $field) {
            if ($this->hasField($field)) {
                yield BooleanField::new($field, $this->labelFor($field));
                break;
            }
        }

        foreach (static::managePublicationDateCandidates() as $field) {
            if ($this->hasField($field)) {
                yield DateTimeField::new($field, $this->labelFor($field))->hideOnForm();
                break;
            }
        }

        if ($this->hasField('description')) {
            yield TextareaField::new('description')->hideOnIndex();
        }

        if ($this->hasField('createdAt')) {
            yield DateTimeField::new('createdAt', 'Created')->hideOnForm();
        }

        if ($this->hasField('updatedAt')) {
            yield DateTimeField::new('updatedAt', 'Updated')->hideOnForm();
        }
    }

    public function publish(AdminContext $context, EntityManagerInterface $entityManager): Response
    {
        $entity = $context->getEntity()->getInstance();
        if (is_object($entity)) {
            $this->setPublicationState($entity, true);
            $entityManager->flush();
        }

        return $this->redirectBack($context->getRequest()->headers->get('referer'));
    }

    public function unpublish(AdminContext $context, EntityManagerInterface $entityManager): Response
    {
        $entity = $context->getEntity()->getInstance();
        if (is_object($entity)) {
            $this->setPublicationState($entity, false);
            $entityManager->flush();
        }

        return $this->redirectBack($context->getRequest()->headers->get('referer'));
    }

    public function batchPublish(BatchActionDto $batchActionDto, EntityManagerInterface $entityManager, RequestStack $requestStack): Response
    {
        $this->setBatchPublicationState($batchActionDto, $entityManager, true);

        return $this->redirectBack($requestStack->getCurrentRequest()?->headers->get('referer'));
    }

    public function batchUnpublish(BatchActionDto $batchActionDto, EntityManagerInterface $entityManager, RequestStack $requestStack): Response
    {
        $this->setBatchPublicationState($batchActionDto, $entityManager, false);

        return $this->redirectBack($requestStack->getCurrentRequest()?->headers->get('referer'));
    }

    /** @return array<string, string> */
    private function defaultSort(): array
    {
        foreach (['updatedAt', 'createdAt', 'publishedAt', 'id'] as $field) {
            if ($this->hasField($field)) {
                return [$field => 'DESC'];
            }
        }

        return [];
    }

    /**
     * @param list<string> $fields
     *
     * @return list<string>
     */
    private function existingFields(array $fields): array
    {
        return array_values(array_filter($fields, fn (string $field): bool => $this->hasField($field)));
    }

    private function hasField(string $field): bool
    {
        try {
            $reflection = new \ReflectionClass(static::getEntityFqcn());
        } catch (\ReflectionException) {
            return false;
        }

        if ($reflection->hasProperty($field)) {
            return true;
        }

        $suffix = ucfirst($field);

        return $reflection->hasMethod('get'.$suffix)
            || $reflection->hasMethod('is'.$suffix)
            || $reflection->hasMethod('has'.$suffix);
    }

    private function supportsPublication(): bool
    {
        return [] !== $this->existingFields(static::managePublicationFlagCandidates())
            || [] !== $this->existingFields(static::managePublicationDateCandidates());
    }

    private function canPublishEntity(object $entity): bool
    {
        if (!$this->supportsPublication()) {
            return false;
        }

        return !$this->isEntityPublished($entity);
    }

    private function canUnpublishEntity(object $entity): bool
    {
        if (!$this->supportsPublication()) {
            return false;
        }

        return $this->isEntityPublished($entity);
    }

    private function isEntityPublished(object $entity): bool
    {
        foreach (static::managePublicationFlagCandidates() as $field) {
            if (!$this->hasField($field)) {
                continue;
            }

            $value = $this->readField($entity, $field);
            if (is_bool($value)) {
                return $value;
            }
        }

        foreach (static::managePublicationDateCandidates() as $field) {
            if (!$this->hasField($field)) {
                continue;
            }

            return null !== $this->readField($entity, $field);
        }

        return false;
    }

    private function setBatchPublicationState(BatchActionDto $batchActionDto, EntityManagerInterface $entityManager, bool $published): void
    {
        $repository = $entityManager->getRepository($batchActionDto->getEntityFqcn());

        foreach ($batchActionDto->getEntityIds() as $entityId) {
            $entity = $repository->find($entityId);
            if (!is_object($entity)) {
                continue;
            }

            $this->setPublicationState($entity, $published);
        }

        $entityManager->flush();
    }

    private function setPublicationState(object $entity, bool $published): void
    {
        foreach (static::managePublicationFlagCandidates() as $field) {
            if ($this->writeField($entity, $field, $published)) {
                break;
            }
        }

        foreach (static::managePublicationDateCandidates() as $field) {
            if (!$this->hasField($field)) {
                continue;
            }

            $this->writeField($entity, $field, $published ? new \DateTimeImmutable() : null);
            break;
        }
    }

    private function readField(object $entity, string $field): mixed
    {
        $suffix = ucfirst($field);
        foreach (['get'.$suffix, 'is'.$suffix, 'has'.$suffix] as $method) {
            if (method_exists($entity, $method)) {
                return $entity->{$method}();
            }
        }

        try {
            $property = new \ReflectionProperty($entity, $field);
            if (!$property->isPublic()) {
                $property->setAccessible(true);
            }

            return $property->getValue($entity);
        } catch (\ReflectionException) {
            return null;
        }
    }

    private function writeField(object $entity, string $field, mixed $value): bool
    {
        if (!$this->hasField($field)) {
            return false;
        }

        $suffix = ucfirst($field);
        foreach (['set'.$suffix, 'mark'.$suffix] as $method) {
            if (method_exists($entity, $method)) {
                $entity->{$method}($value);

                return true;
            }
        }

        try {
            $property = new \ReflectionProperty($entity, $field);
            if (!$property->isPublic()) {
                $property->setAccessible(true);
            }
            $property->setValue($entity, $value);

            return true;
        } catch (\ReflectionException) {
            return false;
        }
    }

    private function humanizeEntityShortName(bool $plural): string
    {
        $shortName = (new \ReflectionClass(static::getEntityFqcn()))->getShortName();
        $words = (string) preg_replace('/(?<!^)[A-Z]/', ' $0', $shortName);

        if ($plural && !str_ends_with($words, 's')) {
            $words .= 's';
        }

        return $words;
    }

    private function labelFor(string $field): string
    {
        $label = (string) preg_replace('/(?<!^)[A-Z]/', ' $0', $field);

        return ucfirst($label);
    }

    private function redirectBack(?string $referer): RedirectResponse
    {
        return $this->redirect($referer ?: '/manage');
    }
}
