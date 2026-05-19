<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Applicating\Entity\ApplicationUser;
use App\Attaching\Entity\Attachment\Attachment;
use App\Commissioning\Entity\CommissionPlanEntity;
use App\Exchanging\Entity\Exchange\Exchange;
use App\Localizing\Entity\TranslationMessage;
use App\Managing\Controller\Crud\Generated\ApplicatingCrudController;
use App\Managing\Controller\Crud\Generated\AttachingCrudController;
use App\Managing\Controller\Crud\Generated\CommissioningCrudController;
use App\Managing\Controller\Crud\Generated\ExchangingCrudController;
use App\Managing\Controller\Crud\Generated\LocalizingCrudController;
use App\Managing\Controller\Crud\Generated\OrderingCrudController;
use App\Managing\Controller\Crud\Generated\PagingCrudController;
use App\Managing\Controller\Crud\Generated\SubscriptingCrudController;
use App\Paging\Entity\Page;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ManageCrudCreateEntityTest extends TestCase
{
    /**
     * @return array<string, array{class-string, class-string}>
     */
    public static function createEntityCases(): array
    {
        return [
            'exchanging' => [ExchangingCrudController::class, Exchange::class],
            'commissioning' => [CommissioningCrudController::class, CommissionPlanEntity::class],
            'localizing' => [LocalizingCrudController::class, TranslationMessage::class],
            'paging' => [PagingCrudController::class, Page::class],
            'attaching' => [AttachingCrudController::class, Attachment::class],
        ];
    }

    #[DataProvider('createEntityCases')]
    public function testCreateEntityHandlesConstructorHeavyEntities(string $controllerClass, string $entityClass): void
    {
        $controller = new $controllerClass();
        $entity = $controller->createEntity($entityClass);

        self::assertInstanceOf($entityClass, $entity);

        match ($entityClass) {
            Exchange::class => $this->assertExchangeDefaults($entity),
            CommissionPlanEntity::class => $this->assertCommissionPlanDefaults($entity),
            TranslationMessage::class => $this->assertTranslationMessageDefaults($entity),
            Page::class => $this->assertPageDefaults($entity),
            default => null,
        };
    }

    #[Test]
    public function testEveryGeneratedCrudControllerCanInstantiateItsNewEntity(): void
    {
        $controllerFiles = glob(dirname(__DIR__, 3).'/src/Controller/Crud/Generated/*CrudController.php');

        self::assertIsArray($controllerFiles);
        self::assertNotEmpty($controllerFiles);

        foreach ($controllerFiles as $controllerFile) {
            $relative = str_replace(dirname(__DIR__, 3).'/src/Controller/Crud/Generated/', '', $controllerFile);
            $controllerClass = 'App\\Managing\\Controller\\Crud\\Generated\\'.str_replace('.php', '', $relative);

            self::assertTrue(class_exists($controllerClass), sprintf('Missing controller class %s', $controllerClass));

            /** @var class-string $controllerClass */
            $controller = new $controllerClass();
            $entityClass = $controllerClass::getEntityFqcn();
            $entity = $controller->createEntity($entityClass);

            self::assertInstanceOf($entityClass, $entity, sprintf('Controller %s should instantiate %s', $controllerClass, $entityClass));
        }
    }

    #[Test]
    public function testNewFormsExposeDomainFieldsBeyondTheMinimalCrudShell(): void
    {
        $subscriptingFields = $this->fieldMap((new SubscriptingCrudController())->configureFields(Crud::PAGE_NEW));
        self::assertArrayHasKey('plan', $subscriptingFields);
        self::assertArrayHasKey('subjectType', $subscriptingFields);
        self::assertArrayHasKey('subjectId', $subscriptingFields);
        self::assertArrayHasKey('status', $subscriptingFields);
        self::assertArrayHasKey('autoRenew', $subscriptingFields);
        self::assertArrayHasKey('trialEndsAt', $subscriptingFields);
        self::assertArrayHasKey('externalReference', $subscriptingFields);
        self::assertContains(AssociationField::class, $this->fieldClasses($subscriptingFields));
        self::assertContains(ChoiceField::class, $this->fieldClasses($subscriptingFields));
        self::assertContains(BooleanField::class, $this->fieldClasses($subscriptingFields));
        self::assertContains(DateTimeField::class, $this->fieldClasses($subscriptingFields));
        self::assertContains(TextField::class, $this->fieldClasses($subscriptingFields));

        $orderingFields = $this->fieldMap((new OrderingCrudController())->configureFields(Crud::PAGE_NEW));
        self::assertArrayHasKey('slug', $orderingFields);
        self::assertArrayHasKey('number', $orderingFields);
        self::assertArrayHasKey('currency', $orderingFields);
        self::assertArrayHasKey('grandTotal', $orderingFields);
        self::assertArrayHasKey('paidTotal', $orderingFields);
        self::assertArrayHasKey('refundedTotal', $orderingFields);
        self::assertArrayHasKey('subtotal', $orderingFields);
        self::assertArrayHasKey('discountTotal', $orderingFields);
        self::assertArrayHasKey('taxTotal', $orderingFields);
        self::assertArrayHasKey('customerId', $orderingFields);
        self::assertArrayHasKey('vendorId', $orderingFields);
        self::assertArrayHasKey('trackingCode', $orderingFields);
        self::assertArrayHasKey('status', $orderingFields);
        self::assertContains(NumberField::class, $this->fieldClasses($orderingFields));
        self::assertContains(TextField::class, $this->fieldClasses($orderingFields));

        $applicatingFields = $this->fieldMap((new ApplicatingCrudController())->configureFields(Crud::PAGE_NEW));
        self::assertArrayHasKey('userIdentifier', $applicatingFields);
        self::assertArrayHasKey('displayName', $applicatingFields);
        self::assertArrayHasKey('roles', $applicatingFields);
        self::assertArrayHasKey('authSource', $applicatingFields);
        self::assertArrayHasKey('active', $applicatingFields);
        self::assertContains(ChoiceField::class, $this->fieldClasses($applicatingFields));
        self::assertContains(BooleanField::class, $this->fieldClasses($applicatingFields));

        self::assertGreaterThanOrEqual(8, count($subscriptingFields));
        self::assertGreaterThanOrEqual(10, count($orderingFields));
        self::assertGreaterThanOrEqual(5, count($applicatingFields));
    }

    #[Test]
    public function testApplicatingCrudCanInstantiateApplicationUserAndExposeRoles(): void
    {
        $controller = new ApplicatingCrudController();
        $entity = $controller->createEntity(ApplicationUser::class);

        self::assertInstanceOf(ApplicationUser::class, $entity);
        self::assertSame([], $entity->getRoles());
        self::assertSame('', $entity->getUserIdentifier());
        self::assertSame('', $entity->getDisplayName());
    }

    private function assertExchangeDefaults(Exchange $exchange): void
    {
        self::assertSame('', $exchange->baseCurrencyCode());
        self::assertSame('', $exchange->quoteCurrencyCode());
    }

    private function assertCommissionPlanDefaults(CommissionPlanEntity $plan): void
    {
        self::assertNull($plan->getId());
        self::assertSame('', $plan->getCode());
        self::assertSame('', $plan->getName());
    }

    private function assertTranslationMessageDefaults(TranslationMessage $message): void
    {
        self::assertNull($message->getId());
        self::assertSame('', $message->getLocaleCode());
        self::assertSame('', $message->getDomainName());
        self::assertSame('', $message->getKeyName());
        self::assertSame('', $message->getMessage());
    }

    private function assertPageDefaults(Page $page): void
    {
        self::assertTrue(null === $page->getId() || (bool) preg_match('/^[a-f0-9]{32}$/', $page->getId()));
        self::assertSame('', $page->getCode());
        self::assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $page->getSlug());
        self::assertSame('', $page->getTitle());
    }

    /**
     * @param iterable<object> $fields
     *
     * @return array<string, string>
     */
    private function fieldMap(iterable $fields): array
    {
        $map = [];
        foreach ($fields as $field) {
            $property = $this->fieldProperty($field);
            $map[$property] = $field::class;
        }

        return $map;
    }

    /**
     * @param array<string, string> $fieldMap
     *
     * @return list<class-string>
     */
    private function fieldClasses(array $fieldMap): array
    {
        return array_values($fieldMap);
    }

    private function fieldProperty(object $field): string
    {
        $reflection = new \ReflectionObject($field);
        $dtoProperty = $reflection->getProperty('dto');
        $dtoProperty->setAccessible(true);
        $dto = $dtoProperty->getValue($field);

        return $dto->getProperty();
    }
}
