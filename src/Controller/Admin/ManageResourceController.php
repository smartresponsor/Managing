<?php

declare(strict_types=1);

namespace App\Managing\Controller\Admin;

use App\Managing\ServiceInterface\Crud\ManageCrudActionUrlBuilderInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudResourceRegistryInterface;
use App\Managing\ServiceInterface\Resource\ManageResourceDetailBuilderInterface;
use App\Managing\Value\ManageCrudResourceDefinition;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ManageResourceController extends AbstractController
{
    public function __construct(
        private readonly ManageCrudResourceRegistryInterface $crudResourceRegistry,
        private readonly ManageCrudActionUrlBuilderInterface $actionUrlBuilder,
        private readonly ManageResourceDetailBuilderInterface $resourceDetailBuilder,
        private readonly ?ManagerRegistry $managerRegistry = null,
    ) {
    }

    #[Route('/manage/resources', name: 'manage_resources', methods: ['GET'])]
    public function index(): Response
    {
        $rows = [];

        foreach ($this->crudResourceRegistry->getCrudResources() as $resource) {
            $rows[] = [
                'resource' => $resource,
                'actions' => $this->actionUrlBuilder->buildActionUrls($resource),
            ];
        }

        return $this->render('manage/admin/content.html.twig', [
            'page_title' => 'Manage resources',
            'content_title' => 'Manage resources',
            'content' => $this->renderView('manage/admin/resources.html.twig', [
                'rows' => $rows,
            ]),
        ]);
    }

    #[Route('/manage/resources/{componentKey}/{resourceKey}/index', name: 'manage_resource_index', methods: ['GET'])]
    public function resourceIndex(string $componentKey, string $resourceKey): Response
    {
        $resource = $this->findResource($componentKey, $resourceKey);

        if (null === $resource) {
            throw $this->createNotFoundException(sprintf('Manage resource "%s.%s" was not found.', $componentKey, $resourceKey));
        }

        $sample = $this->buildEntitySample($resource);

        return $this->render('manage/admin/content.html.twig', [
            'page_title' => $resource->label,
            'content_title' => $resource->label,
            'content' => $this->renderView('manage/admin/resource_index.html.twig', [
                'resource' => $resource,
                'actions' => $this->actionUrlBuilder->buildActionUrls($resource),
                'sample' => $sample,
            ]),
        ]);
    }

    #[Route('/manage/resources/{componentKey}/{resourceKey}/redirect', name: 'manage_resource_redirect', methods: ['GET'])]
    public function resourceRedirect(string $componentKey, string $resourceKey): RedirectResponse
    {
        $resource = $this->findResource($componentKey, $resourceKey);

        if (null === $resource) {
            throw $this->createNotFoundException(sprintf('Manage resource "%s.%s" was not found.', $componentKey, $resourceKey));
        }

        $actions = $this->actionUrlBuilder->buildActionUrls($resource);

        if (isset($actions['index'])) {
            return $this->redirect($actions['index']);
        }

        return $this->redirectToRoute('manage_resource_index', [
            'componentKey' => $componentKey,
            'resourceKey' => $resourceKey,
        ]);
    }

    #[Route('/manage/resources/{componentKey}/{resourceKey}', name: 'manage_resource_detail', methods: ['GET'])]
    public function detail(string $componentKey, string $resourceKey): Response
    {
        $detail = $this->resourceDetailBuilder->buildResourceDetail($componentKey, $resourceKey);

        if (null === $detail) {
            throw $this->createNotFoundException(sprintf('Manage resource "%s.%s" was not found.', $componentKey, $resourceKey));
        }

        return $this->render('manage/admin/content.html.twig', [
            'page_title' => 'Manage resource detail',
            'content_title' => 'Manage resource detail',
            'content' => $this->renderView('manage/admin/resource_detail.html.twig', $detail),
        ]);
    }

    private function findResource(string $componentKey, string $resourceKey): ?ManageCrudResourceDefinition
    {
        foreach ($this->crudResourceRegistry->getCrudResources() as $resource) {
            if ($resource->componentKey === $componentKey && $resource->resourceKey === $resourceKey) {
                return $resource;
            }
        }

        return null;
    }

    /** @return array{available: bool, columns: list<string>, rows: list<array<string, mixed>>, note: string|null} */
    private function buildEntitySample(ManageCrudResourceDefinition $resource): array
    {
        if (null === $this->managerRegistry) {
            return [
                'available' => false,
                'columns' => [],
                'rows' => [],
                'note' => 'Doctrine manager registry is not available in this host application.',
            ];
        }

        $manager = $this->managerRegistry->getManagerForClass($resource->entityClass);
        if (null === $manager) {
            return [
                'available' => false,
                'columns' => [],
                'rows' => [],
                'note' => 'No Doctrine manager is registered for this entity class.',
            ];
        }

        try {
            $metadata = $manager->getClassMetadata($resource->entityClass);
            $columns = array_values(array_slice($metadata->getFieldNames(), 0, 8));
            $records = $manager->getRepository($resource->entityClass)->findBy([], null, 30);
        } catch (\Throwable $exception) {
            return [
                'available' => false,
                'columns' => [],
                'rows' => [],
                'note' => $exception->getMessage(),
            ];
        }

        $rows = [];
        foreach ($records as $record) {
            $row = [];
            foreach ($columns as $field) {
                $row[$field] = $this->readFieldValue($record, $field);
            }
            $rows[] = $row;
        }

        return [
            'available' => true,
            'columns' => $columns,
            'rows' => $rows,
            'note' => null,
        ];
    }

    private function readFieldValue(object $record, string $field): mixed
    {
        $accessors = [
            'get'.ucfirst($field),
            'is'.ucfirst($field),
            $field,
        ];

        foreach ($accessors as $accessor) {
            if (method_exists($record, $accessor)) {
                try {
                    return $this->normalizeValue($record->{$accessor}());
                } catch (\Throwable) {
                    return '[unreadable]';
                }
            }
        }

        return '[not exposed]';
    }

    private function normalizeValue(mixed $value): string
    {
        if (null === $value) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return '['.get_debug_type($value).']';
    }
}
