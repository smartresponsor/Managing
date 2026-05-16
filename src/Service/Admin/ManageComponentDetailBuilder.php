<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageComponentDetailBuilderInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudActionUrlBuilderInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudResourceRegistryInterface;
use App\Managing\ServiceInterface\Form\ManageFormRegistryInterface;
use App\Managing\ServiceInterface\Probe\ManageProbeRegistryInterface;
use App\Managing\ServiceInterface\Relation\ManageRelationRegistryInterface;
use App\Managing\ServiceInterface\Route\ManageRouteRegistryInterface;
use App\Managing\Value\ManageComponentDefinition;
use App\Managing\Value\ManageCrudResourceDefinition;
use App\Managing\Value\ManageRouteDefinition;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class ManageComponentDetailBuilder implements ManageComponentDetailBuilderInterface
{
    public function __construct(
        private array $businessIndexResources,
        private array $businessIndexRoutes,
        private ManageAdminRegistryInterface $adminRegistry,
        private ManageCrudResourceRegistryInterface $crudResourceRegistry,
        private ManageCrudActionUrlBuilderInterface $actionUrlBuilder,
        private ManageRouteRegistryInterface $routeRegistry,
        private UrlGeneratorInterface $urlGenerator,
        private ManageFormRegistryInterface $formRegistry,
        private ManageRelationRegistryInterface $relationRegistry,
        private ManageProbeRegistryInterface $probeRegistry,
        private ?ManagerRegistry $managerRegistry = null,
    ) {
    }

    public function buildComponentDetail(string $componentKey): ?array
    {
        $component = $this->findComponent($componentKey);

        if (!$component instanceof ManageComponentDefinition) {
            return null;
        }

        $businessIndex = $this->buildBusinessIndex($componentKey);

        return [
            'component' => $component,
            'businessIndex' => $businessIndex,
        ];
    }

    private function findComponent(string $componentKey): ?ManageComponentDefinition
    {
        foreach ($this->adminRegistry->getComponents() as $component) {
            if ($component->key === $componentKey) {
                return $component;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildBusinessIndex(string $componentKey): ?array
    {
        $resource = $this->findBusinessResource($componentKey);

        if (!$resource instanceof ManageCrudResourceDefinition) {
            $routes = $this->buildBusinessRoutes($componentKey);
            if ([] === $routes) {
                return null;
            }

            return [
                'kind' => 'routes',
                'routes' => $routes,
            ];
        }

        return [
            'kind' => 'resource',
            'resource' => $resource,
            'actions' => $this->actionUrlBuilder->buildActionUrls($resource),
            'sample' => $this->buildEntitySample($resource),
        ];
    }

    private function findBusinessResource(string $componentKey): ?ManageCrudResourceDefinition
    {
        $allowedResourceKeys = $this->businessIndexResources[$componentKey] ?? null;
        $hasAllowlist = is_array($allowedResourceKeys) && [] !== $allowedResourceKeys;
        $fallback = null;

        foreach ($this->crudResourceRegistry->getCrudResources() as $resource) {
            if (!property_exists($resource, 'componentKey') || $resource->componentKey !== $componentKey) {
                continue;
            }

            if ($hasAllowlist && !in_array($resource->resourceKey, $allowedResourceKeys, true)) {
                continue;
            }

            if (ManageCrudResourceDefinition::MODE_EASYADMIN === $resource->mode && null !== $resource->crudControllerClass) {
                return $resource;
            }

            if (null === $fallback && $hasAllowlist) {
                $fallback = $resource;
            }
        }

        return $fallback;
    }

    /**
     * @return list<array{route: ManageRouteDefinition, detailUrl: string}>
     */
    private function buildBusinessRoutes(string $componentKey): array
    {
        $allowedRouteNames = $this->businessIndexRoutes[$componentKey] ?? null;
        $hasAllowlist = is_array($allowedRouteNames) && [] !== $allowedRouteNames;
        $rows = [];

        foreach ($this->routeRegistry->getRoutes() as $route) {
            if ($route->componentKey !== $componentKey) {
                continue;
            }

            if ($hasAllowlist && !in_array($route->routeName, $allowedRouteNames, true)) {
                continue;
            }

            $rows[] = [
                'route' => $route,
                'detailUrl' => $this->urlGenerator->generate('manage_route_detail', [
                    'componentKey' => $route->componentKey,
                    'routeName' => $route->routeName,
                ]),
            ];
        }

        return $rows;
    }

    /**
     * @return array{available: bool, columns: list<string>, rows: list<array<string, mixed>>, note: string|null}
     */
    private function buildEntitySample(ManageCrudResourceDefinition $resource): array
    {
        if (null === $this->managerRegistry) {
            return [
                'available' => false,
                'columns' => [],
                'rows' => [],
                'note' => 'Doctrine manager registry is not available.',
            ];
        }

        try {
            $manager = $this->managerRegistry->getManagerForClass($resource->entityClass);
        } catch (\Throwable $exception) {
            return [
                'available' => false,
                'columns' => [],
                'rows' => [],
                'note' => $exception->getMessage(),
            ];
        }

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
