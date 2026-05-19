<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
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

    protected static function manageIsReadOnly(): bool
    {
        return false;
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
            ->setDefaultRowAction(static::manageIsReadOnly() ? Action::DETAIL : Action::EDIT);

        return $crud;
    }

    public function configureActions(Actions $actions): Actions
    {
        if (static::manageIsReadOnly()) {
            return $actions
                ->add(Crud::PAGE_INDEX, Action::DETAIL)
                ->disable(Action::NEW, Action::EDIT, Action::DELETE);
        }

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
            yield IdField::new('id', 'ID')
                ->formatValue(fn (mixed $value): string => $this->stringifyFieldValue($value))
                ->onlyOnIndex();
        }

        foreach (['firstTitle', 'title', 'name', 'label'] as $field) {
            if ($this->hasField($field)) {
                yield TextField::new($field, $this->labelFor($field))->setMaxLength(96);
                break;
            }
        }

        foreach (['code', 'slug', 'sku'] as $field) {
            if ($this->hasField($field)) {
                yield TextField::new($field, $this->labelFor($field))->setMaxLength(64);
            }
        }

        foreach (static::manageStatusFieldCandidates() as $field) {
            if ($this->hasField($field)) {
                $enumType = $this->enumTypeFor($field);

                if (null !== $enumType) {
                    yield ChoiceField::new($field, $this->labelFor($field))
                        ->setChoices($this->enumChoices($enumType));
                } else {
                    yield TextField::new($field, $this->labelFor($field))
                        ->setMaxLength(32)
                        ->setTemplatePath('admin/field/stringified_text.html.twig')
                        ->formatValue(fn (mixed $value): string => $this->stringifyFieldValue($value));
                }
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

        foreach ($this->discoverFormFields($pageName) as $field) {
            yield $field;
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
        $label = (string) preg_replace('/(?<!^)[A-Z_]/', ' $0', $field);
        $label = str_replace('_', ' ', $label);

        return ucfirst($label);
    }

    /**
     * @return iterable<int, object>
     */
    private function discoverFormFields(string $pageName): iterable
    {
        if (!\in_array($pageName, [Crud::PAGE_NEW, Crud::PAGE_EDIT], true)) {
            return [];
        }

        $excludedFields = array_fill_keys([
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
            'published',
            'isPublished',
            'enabled',
            'active',
            'publishedAt',
            'publishAt',
            'description',
            'createdAt',
            'updatedAt',
        ], true);

        try {
            $reflection = new \ReflectionClass(static::getEntityFqcn());
        } catch (\ReflectionException) {
            return [];
        }

        $fields = [];
        foreach ($reflection->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $fieldName = $property->getName();
            if (isset($excludedFields[$fieldName])) {
                continue;
            }

            $field = $this->formFieldForProperty($property);
            if (null !== $field) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    private function formFieldForProperty(\ReflectionProperty $property): mixed
    {
        if ($field = $this->associationFieldForProperty($property)) {
            return $field;
        }

        $column = $this->attributeInstance($property, ORM\Column::class);
        if (null === $column) {
            return null;
        }

        $fieldName = $property->getName();
        $label = $this->labelFor($fieldName);

        if (isset($column->enumType) && is_string($column->enumType) && '' !== $column->enumType) {
            return ChoiceField::new($fieldName, $label)
                ->setChoices($this->enumChoices($column->enumType));
        }

        $propertyType = $this->propertyTypeName($property);
        if ('array' === $propertyType) {
            $field = $this->choiceFieldForArrayProperty($property);
            if (null !== $field) {
                return $field;
            }
        }

        if ('bool' === $propertyType) {
            return BooleanField::new($fieldName, $label);
        }

        if ('int' === $propertyType) {
            return IntegerField::new($fieldName, $label);
        }

        if ('float' === $propertyType) {
            return NumberField::new($fieldName, $label);
        }

        if (\DateTimeImmutable::class === $propertyType || \DateTimeInterface::class === $propertyType || \DateTime::class === $propertyType) {
            return DateTimeField::new($fieldName, $label);
        }

        return match ($column->type ?? 'string') {
            'boolean' => BooleanField::new($fieldName, $label),
            'integer', 'smallint', 'bigint' => IntegerField::new($fieldName, $label),
            'float', 'decimal' => NumberField::new($fieldName, $label),
            'date', 'date_immutable' => DateField::new($fieldName, $label),
            'datetime', 'datetime_immutable' => DateTimeField::new($fieldName, $label),
            'time', 'time_immutable' => TimeField::new($fieldName, $label),
            'text' => TextareaField::new($fieldName, $label),
            'guid' => $this->guidFieldForProperty($fieldName, $label),
            default => $this->stringFieldForProperty($fieldName, $label, $column->length ?? null),
        };
    }

    private function associationFieldForProperty(\ReflectionProperty $property): ?object
    {
        $fieldName = $property->getName();
        $label = $this->labelFor($fieldName);

        foreach ([ORM\ManyToOne::class, ORM\OneToOne::class] as $attributeClass) {
            $attribute = $this->attributeInstance($property, $attributeClass);
            if (null === $attribute) {
                continue;
            }

            return AssociationField::new($fieldName, $label)
                ->renderAsNativeWidget()
                ->setFormTypeOption('choice_label', fn (object $choice): string => $this->associationChoiceLabel($choice));
        }

        return null;
    }

    private function guidFieldForProperty(string $fieldName, string $label): object
    {
        if ($this->looksLikeEmailField($fieldName)) {
            return EmailField::new($fieldName, $label);
        }

        if ($this->looksLikeUrlField($fieldName)) {
            return UrlField::new($fieldName, $label);
        }

        return TextField::new($fieldName, $label);
    }

    private function choiceFieldForArrayProperty(\ReflectionProperty $property): ?object
    {
        $fieldName = $property->getName();
        $label = $this->labelFor($fieldName);

        if ('roles' !== $fieldName) {
            return null;
        }

        $choices = $this->knownRoleChoicesForEntity();
        if ([] === $choices) {
            return null;
        }

        return ChoiceField::new($fieldName, $label)
            ->setChoices($choices)
            ->allowMultipleChoices()
            ->renderExpanded(false)
            ->autocomplete();
    }

    /**
     * @return array<string, string>
     */
    private function knownRoleChoicesForEntity(): array
    {
        $entityFqcn = static::getEntityFqcn();
        if (\App\Applicating\Entity\ApplicationUser::class === $entityFqcn) {
            return [
                'Application admin' => 'ROLE_APPLICATION_ADMIN',
                'Application manager' => 'ROLE_APPLICATION_MANAGER',
                'Application viewer' => 'ROLE_APPLICATION_VIEWER',
                'Application user' => 'ROLE_APPLICATION_USER',
            ];
        }

        return [];
    }

    private function stringFieldForProperty(string $fieldName, string $label, ?int $length): object
    {
        if ($this->looksLikeEmailField($fieldName)) {
            return EmailField::new($fieldName, $label);
        }

        if ($this->looksLikeUrlField($fieldName)) {
            return UrlField::new($fieldName, $label);
        }

        if ($this->looksLikeLongTextField($fieldName) || (null !== $length && $length > 255)) {
            return TextareaField::new($fieldName, $label);
        }

        return TextField::new($fieldName, $label);
    }

    private function looksLikeEmailField(string $fieldName): bool
    {
        return str_contains(strtolower($fieldName), 'email');
    }

    private function looksLikeUrlField(string $fieldName): bool
    {
        $fieldName = strtolower($fieldName);

        return str_contains($fieldName, 'url') || str_contains($fieldName, 'link');
    }

    private function looksLikeLongTextField(string $fieldName): bool
    {
        $fieldName = strtolower($fieldName);

        return str_contains($fieldName, 'description')
            || str_contains($fieldName, 'summary')
            || str_contains($fieldName, 'message')
            || str_contains($fieldName, 'content')
            || str_contains($fieldName, 'body')
            || str_contains($fieldName, 'note')
            || str_contains($fieldName, 'payload')
            || str_contains($fieldName, 'details');
    }

    private function associationChoiceLabel(object $choice): string
    {
        if (method_exists($choice, '__toString')) {
            try {
                $label = (string) $choice;
                if ('' !== trim($label)) {
                    return $label;
                }
            } catch (\Throwable) {
                // Fall through to reflective label guessing.
            }
        }

        foreach (['firstTitle', 'title', 'name', 'label', 'code', 'slug', 'number', 'reference', 'identifier', 'email', 'username'] as $field) {
            $value = $this->readField($choice, $field);
            if (null === $value) {
                continue;
            }

            $label = $this->stringifyFieldValue($value);
            if ('' !== trim($label)) {
                return $label;
            }
        }

        if (method_exists($choice, 'getId')) {
            $value = $choice->getId();
            if (null !== $value) {
                return $this->stringifyFieldValue($value);
            }
        }

        if (method_exists($choice, 'id')) {
            $value = $choice->id();
            if (null !== $value) {
                return $this->stringifyFieldValue($value);
            }
        }

        return get_debug_type($choice);
    }

    private function propertyTypeName(\ReflectionProperty $property): ?string
    {
        $type = $property->getType();
        if ($type instanceof \ReflectionNamedType) {
            return $type->getName();
        }

        if ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $namedType) {
                if ($namedType->allowsNull()) {
                    continue;
                }

                return $namedType->getName();
            }

            $namedTypes = $type->getTypes();
            if ([] !== $namedTypes) {
                return $namedTypes[0]->getName();
            }
        }

        return null;
    }

    private function attributeInstance(\ReflectionProperty $property, string $attributeClass): ?object
    {
        $attributes = $property->getAttributes($attributeClass);
        if ([] === $attributes) {
            return null;
        }

        try {
            return $attributes[0]->newInstance();
        } catch (\Throwable) {
            return null;
        }
    }

    private function enumTypeFor(string $field): ?string
    {
        try {
            $reflection = new \ReflectionClass(static::getEntityFqcn());
            if (!$reflection->hasProperty($field)) {
                return null;
            }

            $property = $reflection->getProperty($field);
            $attributes = $property->getAttributes(ORM\Column::class);
            foreach ($attributes as $attribute) {
                $instance = $attribute->newInstance();
                if (isset($instance->enumType) && is_string($instance->enumType) && '' !== $instance->enumType) {
                    return $instance->enumType;
                }
            }
        } catch (\ReflectionException) {
            return null;
        }

        return null;
    }

    /**
     * @return array<string, \BackedEnum>
     */
    private function enumChoices(string $enumType): array
    {
        $choices = [];
        foreach ($enumType::cases() as $case) {
            $choices[ucfirst(str_replace('_', ' ', $case->value))] = $case;
        }

        return $choices;
    }

    private function redirectBack(?string $referer): RedirectResponse
    {
        return $this->redirect($referer ?: '/manage');
    }

    public function createEntity(string $entityFqcn): object
    {
        return $this->instantiateEntityForNewForm($entityFqcn);
    }

    private function instantiateEntityForNewForm(string $entityFqcn, array $stack = []): object
    {
        try {
            $reflectionClass = new \ReflectionClass($entityFqcn);
        } catch (\ReflectionException) {
            return new $entityFqcn();
        }

        if (!$reflectionClass->isInstantiable()) {
            return $reflectionClass->newInstanceWithoutConstructor();
        }

        $constructor = $reflectionClass->getConstructor();
        if (null === $constructor || [] === $constructor->getParameters()) {
            return $reflectionClass->newInstance();
        }

        if (in_array($entityFqcn, $stack, true)) {
            return $reflectionClass->newInstanceWithoutConstructor();
        }

        $stack[] = $entityFqcn;

        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            $arguments[] = $this->resolveConstructorArgument($parameter, $stack);
        }

        try {
            return $reflectionClass->newInstanceArgs($arguments);
        } catch (\Throwable) {
            return $reflectionClass->newInstanceWithoutConstructor();
        }
    }

    private function resolveConstructorArgument(\ReflectionParameter $parameter, array $stack): mixed
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $type = $parameter->getType();
        if ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $namedType) {
                $value = $this->resolveNamedConstructorArgument($namedType, $stack);
                if (null !== $value || $namedType->allowsNull()) {
                    return $value;
                }
            }

            return null;
        }

        if ($type instanceof \ReflectionNamedType) {
            return $this->resolveNamedConstructorArgument($type, $stack);
        }

        return null;
    }

    private function resolveNamedConstructorArgument(\ReflectionNamedType $type, array $stack): mixed
    {
        if ($type->allowsNull()) {
            return null;
        }

        if ($type->isBuiltin()) {
            return match ($type->getName()) {
                'string' => '',
                'int' => 0,
                'float' => 0.0,
                'bool' => false,
                'array' => [],
                'callable' => static fn (): null => null,
                'iterable' => [],
                'mixed' => null,
                default => null,
            };
        }

        $typeName = $type->getName();

        if (\DateTimeImmutable::class === $typeName || \DateTimeInterface::class === $typeName || \DateTime::class === $typeName) {
            return new \DateTimeImmutable();
        }

        if (enum_exists($typeName)) {
            $cases = $typeName::cases();

            return $cases[0] ?? null;
        }

        if (!class_exists($typeName)) {
            return null;
        }

        try {
            return $this->instantiateEntityForNewForm($typeName, $stack);
        } catch (\Throwable) {
            $reflectionClass = new \ReflectionClass($typeName);

            return $reflectionClass->isInstantiable() ? $reflectionClass->newInstanceWithoutConstructor() : null;
        }
    }

    private function stringifyFieldValue(mixed $value): string
    {
        if (null === $value) {
            return '';
        }

        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return false === $encoded ? 'array' : $encoded;
        }

        return (string) get_debug_type($value);
    }
}
