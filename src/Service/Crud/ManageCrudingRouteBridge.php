<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use App\Managing\ServiceInterface\Crud\ManageCrudingRouteBridgeInterface;
use App\Managing\Value\ManageCrudResourceDefinition;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class ManageCrudingRouteBridge implements ManageCrudingRouteBridgeInterface
{
    private const CRUD_CONTEXT_CLASS = 'App\\Cruding\\Dto\\Crud\\CrudContext';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ?object $crudRouteNameResolver = null,
    ) {
    }

    public function isAvailable(): bool
    {
        return class_exists(self::CRUD_CONTEXT_CLASS)
            && null !== $this->crudRouteNameResolver;
    }

    public function buildActionUrls(ManageCrudResourceDefinition $resource): array
    {
        if (!$this->isAvailable() || !$resource->enabled || null === $resource->resourcePath) {
            return [];
        }

        $resolver = $this->crudRouteNameResolver;
        $context = $this->createCrudContext($resource);
        $urls = [];

        foreach ([
            'index' => 'resolveIndex',
            'new' => 'resolveNew',
        ] as $action => $method) {
            if (!method_exists($resolver, $method) || !method_exists($resolver, 'parameters')) {
                continue;
            }

            try {
                $routeName = $resolver->{$method}($context);
                $parameters = $resolver->parameters($context, null, $resource->identifierField);
                $urls[$action] = $this->urlGenerator->generate($routeName, $parameters);
            } catch (RouteNotFoundException) {
                continue;
            }
        }

        return $urls;
    }

    private function createCrudContext(ManageCrudResourceDefinition $resource): object
    {
        $class = self::CRUD_CONTEXT_CLASS;

        return new $class(
            surface: $resource->surface,
            operation: 'index',
            resourcePath: $resource->resourcePath ?? $resource->resourceKey,
            entityClass: $resource->entityClass,
            identifierField: $resource->identifierField,
            identifierValue: null,
            formTypeClass: $resource->formTypeClass,
            templatePrefix: $resource->templatePrefix,
        );
    }
}
