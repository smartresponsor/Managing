<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin\Host;

use App\Managing\Value\ManageCrudResourceDefinition;

final class ManageHostCrudResourceFactory
{
    public function __construct(
        private readonly ManageHostClassNameResolver $classNameResolver,
        private readonly ManageHostCrudControllerResolver $crudControllerResolver,
    ) {
    }

    public function create(string $className): ManageCrudResourceDefinition
    {
        $componentKey = $this->classNameResolver->componentKeyFromClass($className);
        $shortName = $this->classNameResolver->shortClassName($className);

        return new ManageCrudResourceDefinition(
            componentKey: $componentKey,
            resourceKey: $this->classNameResolver->resourceKeyFromClass($className),
            label: $this->classNameResolver->humanize($shortName),
            entityClass: $className,
            crudControllerClass: $this->crudControllerResolver->discoverCrudControllerClass($className),
            formTypeClass: null,
            routeNamePattern: null,
            menuGroup: $this->classNameResolver->componentLabel($componentKey),
            enabled: true,
            mode: ManageCrudResourceDefinition::MODE_EASYADMIN,
            resourcePath: sprintf('%s/%s', $componentKey, $this->classNameResolver->resourcePathSegmentFromClass($className)),
            identifierField: 'id',
            surface: ManageCrudResourceDefinition::SURFACE_MANAGE,
            templatePrefix: 'crud',
        );
    }
}
