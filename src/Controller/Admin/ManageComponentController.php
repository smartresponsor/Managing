<?php

declare(strict_types=1);

namespace App\Managing\Controller\Admin;

use App\Managing\Service\Admin\ManageComponentResolver;
use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageContributionFilterInterface;
use App\Managing\Value\ManageCrudResourceDefinition;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Collection\ActionCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\EntityCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\CrudContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

final class ManageComponentController extends AbstractController
{
    public function __construct(
        private readonly ManageAdminRegistryInterface $adminRegistry,
        private readonly ManageContributionFilterInterface $contributionFilter,
        private readonly ManageComponentResolver $componentResolver,
        private readonly ManagerRegistry $managerRegistry,
        private readonly RequestStack $requestStack,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function index(Request $request): Response
    {
        $componentToken = $this->extractComponentToken($request);
        $componentKey = $this->componentResolver->resolve($componentToken);
        if ('' === $componentKey || !$this->contributionFilter->isComponentEnabled($componentKey)) {
            return $this->failAndRedirect($componentKey, sprintf('Unknown component "%s".', $componentKey));
        }

        $resource = $this->selectPrimaryResource($componentKey);
        if (null === $resource) {
            return $this->failAndRedirect($componentKey, sprintf('No business resource is configured for component "%s".', $componentKey));
        }

        $records = $this->loadRecords($resource, 30);
        $baseContext = $request->attributes->get(EA::CONTEXT_REQUEST_ATTRIBUTE);
        if (!$baseContext instanceof AdminContext) {
            return $this->failAndRedirect($componentKey, sprintf('Missing EasyAdmin context for component "%s".', $componentKey));
        }

        $context = $this->withCrudContext($baseContext);
        $request->attributes->set(EA::CONTEXT_REQUEST_ATTRIBUTE, $context);

        $context->getCrud()->setEntityFqcn($resource->entityClass);
        $context->getCrud()->setCurrentAction(Crud::PAGE_INDEX);
        $context->getCrud()->setPageName(Crud::PAGE_INDEX);
        $context->getCrud()->setEntityLabelInSingular($resource->label);
        $context->getCrud()->setEntityLabelInPlural($resource->label.' records');
        $context->getCrud()->setCustomPageTitle(Crud::PAGE_INDEX, ucfirst($componentKey).' index');
        $context->getCrud()->setSearchFields(null);
        $context->getCrud()->setDefaultRowAction(null);
        $context->getCrud()->setShowEntityActionsAsDropdown(false);

        $entities = new EntityCollection();
        foreach ($records as $record) {
            $entityDto = $this->createEntityDto($resource, $record);
            $entityDto->setFields($this->createFields($record, $resource));
            $entityDto->setActions(new ActionCollection([]));
            $entities->set($entityDto);
        }

        return $this->render('@EasyAdmin/crud/index.html.twig', [
            'entities' => $entities,
            'paginator' => $this->componentPaginator($records, $request->getUri()),
            'filters' => [],
            'global_actions' => [],
            'batch_actions' => [],
        ]);
    }

    private function extractComponentToken(Request $request): string
    {
        $request = $this->requestStack->getMainRequest() ?? $request;

        $requestUri = (string) parse_url($request->getRequestUri(), PHP_URL_PATH);
        if ('' !== $requestUri) {
            $segments = array_values(array_filter(explode('/', strtolower(trim($requestUri, '/')))));
            $token = [] === $segments ? '' : (string) end($segments);
            if ('' !== $token && 'manage' !== $token) {
                return $token;
            }
        }

        $token = (string) $request->attributes->get('componentKey', '');
        if ('' !== $token && 'manage' !== $token) {
            return $token;
        }

        $routeParams = $request->attributes->get('_route_params');
        if (is_array($routeParams) && isset($routeParams['componentKey']) && is_string($routeParams['componentKey'])) {
            $token = $routeParams['componentKey'];
            if ('' !== $token && 'manage' !== $token) {
                return $token;
            }
        }

        $pathInfo = trim($request->getPathInfo(), '/');
        if ('' === $pathInfo) {
            return '';
        }

        $segments = array_values(array_filter(explode('/', strtolower($pathInfo))));

        return [] === $segments ? '' : (string) end($segments);
    }

    private function failAndRedirect(string $componentKey, string $message): Response
    {
        $this->logger->warning($message, [
            'componentKey' => $componentKey,
            'route' => 'manage_component',
            'requestUri' => $this->currentRequestUri(),
        ]);
        $this->addFlash('warning', $message);

        return $this->redirectToRoute('manage');
    }

    private function currentRequestUri(): string
    {
        return (string) ($_SERVER['REQUEST_URI'] ?? '');
    }

    /**
     * @return list<ManageCrudResourceDefinition>
     */
    private function componentResources(string $componentKey): array
    {
        $resources = array_values(array_filter(
            $this->adminRegistry->getCrudResources(),
            static fn (ManageCrudResourceDefinition $resource): bool => $resource->componentKey === $componentKey,
        ));

        usort(
            $resources,
            function (ManageCrudResourceDefinition $left, ManageCrudResourceDefinition $right) use ($componentKey): int {
                $rightScore = $this->resourceScore($right, $componentKey);
                $leftScore = $this->resourceScore($left, $componentKey);

                if ($rightScore !== $leftScore) {
                    return $rightScore <=> $leftScore;
                }

                return [$left->resourceKey, $left->label] <=> [$right->resourceKey, $right->label];
            },
        );

        return $resources;
    }

    private function selectPrimaryResource(string $componentKey): ?ManageCrudResourceDefinition
    {
        $resources = $this->componentResources($componentKey);
        if ([] === $resources) {
            return null;
        }

        foreach ($resources as $resource) {
            $manager = $this->managerRegistry->getManagerForClass($resource->entityClass);
            if (null === $manager) {
                continue;
            }

            try {
                $repository = $manager->getRepository($resource->entityClass);
                if ([] !== $repository->findBy([], null, 1)) {
                    return $resource;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $resources[0] ?? null;
    }

    /**
     * @return list<object>
     */
    private function loadRecords(ManageCrudResourceDefinition $resource, int $limit): array
    {
        $manager = $this->managerRegistry->getManagerForClass($resource->entityClass);
        if (null === $manager) {
            return [];
        }

        try {
            $metadata = $manager->getClassMetadata($resource->entityClass);
            $orderBy = [];
            foreach ($metadata->getIdentifierFieldNames() as $identifierFieldName) {
                $orderBy[$identifierFieldName] = 'DESC';
                break;
            }

            $records = $manager->getRepository($resource->entityClass)->findBy([], $orderBy, $limit);

            return array_values(array_filter($records, static fn (mixed $record): bool => is_object($record)));
        } catch (\Throwable) {
            return [];
        }
    }

    private function createEntityDto(ManageCrudResourceDefinition $resource, object $record): EntityDto
    {
        $manager = $this->managerRegistry->getManagerForClass($resource->entityClass);
        if (null !== $manager) {
            $metadata = $manager->getClassMetadata($resource->entityClass);
        } else {
            $metadata = new ClassMetadata($resource->entityClass);
            $metadata->setIdentifier(['id']);
        }

        return new EntityDto($resource->entityClass, $metadata, null, $record);
    }

    private function createFields(object $record, ManageCrudResourceDefinition $resource): FieldCollection
    {
        $manager = $this->managerRegistry->getManagerForClass($resource->entityClass);
        if (null !== $manager) {
            $metadata = $manager->getClassMetadata($resource->entityClass);
        } else {
            $metadata = new ClassMetadata($resource->entityClass);
            $metadata->setIdentifier(['id']);
        }

        $fieldNames = $this->selectFieldNames($metadata);
        $fields = [];

        foreach ($fieldNames as $fieldName) {
            $value = $this->readProperty($record, $fieldName);
            $label = $this->humanize($fieldName);

            if (is_bool($value)) {
                $field = BooleanField::new($fieldName, $label);
                $field->setValue($value);
                $field->setFormattedValue($value);
            } else {
                $field = TextField::new($fieldName, $label);
                $field->setValue($value);
                $field->setFormattedValue($this->stringifyValue($value));
            }

            $field->setSortable(false);
            $fields[] = $field->getAsDto();
        }

        return new FieldCollection($fields);
    }

    /**
     * @return list<string>
     */
    private function selectFieldNames(ClassMetadata $metadata): array
    {
        $fieldNames = [];
        foreach ($metadata->getIdentifierFieldNames() as $identifierFieldName) {
            $fieldNames[] = $identifierFieldName;
        }

        foreach ($metadata->getFieldNames() as $fieldName) {
            if (in_array($fieldName, $fieldNames, true)) {
                continue;
            }

            $fieldNames[] = $fieldName;
            if (count($fieldNames) >= 6) {
                break;
            }
        }

        return $fieldNames;
    }

    private function stringifyValue(mixed $value): string
    {
        if (null === $value) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof \UnitEnum) {
            return $value->name;
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        if (is_object($value)) {
            return $value::class;
        }

        return (string) $value;
    }

    private function humanize(string $value): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $value));
    }

    private function resourceScore(ManageCrudResourceDefinition $resource, string $componentKey): int
    {
        $subject = strtolower($resource->resourceKey.' '.$resource->label.' '.$resource->entityClass);
        $score = 0;

        if (str_contains($subject, $componentKey)) {
            $score += 100;
        }

        if (str_ends_with($resource->resourceKey, '_entity')) {
            $score += 10;
        }

        foreach ([
            'attachment', 'audit', 'binding', 'change_request', 'control', 'delivery', 'destination',
            'event', 'history', 'idempotency', 'log', 'member', 'metric', 'outbox', 'pin', 'projection',
            'relation', 'review', 'snapshot', 'token', 'workflow', 'queue', 'batch',
        ] as $technicalKeyword) {
            if (str_contains($subject, $technicalKeyword)) {
                $score -= 20;
            }
        }

        foreach ([
            'account', 'address', 'catalog', 'category', 'commission', 'content', 'currency', 'exchange',
            'message', 'order', 'page', 'payment', 'profile', 'product', 'record', 'shipment', 'tag',
            'tax', 'user', 'vendor',
        ] as $businessKeyword) {
            if (str_contains($subject, $businessKeyword)) {
                $score += 10;
            }
        }

        return $score;
    }

    private function componentPaginator(array $records, string $uri): object
    {
        $count = count($records);

        return new class($count, $uri) {
            public function __construct(
                private readonly int $numResults,
                private readonly string $uri,
            ) {
            }

            public function getNumResults(): int
            {
                return $this->numResults;
            }

            public function hasPreviousPage(): bool
            {
                return false;
            }

            public function hasNextPage(): bool
            {
                return false;
            }

            public function getCurrentPage(): int
            {
                return 1;
            }

            public function getPageRange(): array
            {
                return [1];
            }

            public function getPreviousPage(): int
            {
                return 1;
            }

            public function getNextPage(): int
            {
                return 1;
            }

            public function generateUrlForPage(int $page): string
            {
                return $this->uri;
            }
        };
    }

    private function withCrudContext(AdminContext $baseContext): AdminContext
    {
        $crudContext = CrudContext::forTesting();

        return new AdminContext(
            $this->reflectProperty($baseContext, 'requestContext'),
            $crudContext,
            $this->reflectProperty($baseContext, 'dashboardContext'),
            $this->reflectProperty($baseContext, 'i18nContext'),
        );
    }

    private function readProperty(object $object, string $propertyName): mixed
    {
        try {
            $reflection = new \ReflectionProperty($object, $propertyName);
            $reflection->setAccessible(true);

            return $reflection->getValue($object);
        } catch (\ReflectionException) {
            return null;
        }
    }

    private function reflectProperty(object $object, string $propertyName): object
    {
        try {
            $reflection = new \ReflectionClass($object);
            $property = $reflection->getProperty($propertyName);
            $property->setAccessible(true);

            return $property->getValue($object);
        } catch (\ReflectionException $exception) {
            throw new NotFoundHttpException(sprintf('Failed to read admin context property "%s".', $propertyName), previous: $exception);
        }
    }
}
