<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin\Host;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Controller\CrudControllerInterface;

final class ManageHostCrudControllerResolver
{
    public function __construct(private readonly ManageHostClassNameResolver $classNameResolver)
    {
    }

    public function discoverCrudControllerClass(string $entityClass): ?string
    {
        $parts = explode('\\', $entityClass);
        $shortName = $this->classNameResolver->shortClassName($entityClass);
        $componentPrefix = isset($parts[1]) && !in_array($parts[1], ['Entity', 'Form', 'Controller'], true) ? $parts[1] : '';
        $componentKey = $this->classNameResolver->componentKeyFromClass($entityClass);

        $candidates = [];
        $candidates[] = sprintf('App\\Managing\\Controller\\Crud\\Generated\\%sCrudController', $this->classNameResolver->studly($componentKey));

        if (str_contains($entityClass, '\\Entity\\')) {
            $candidates[] = str_replace('\\Entity\\', '\\Controller\\Crud\\', $entityClass).'CrudController';
        }

        if ('' !== $componentPrefix && str_contains($entityClass, sprintf('\\%s\\Entity\\', $componentPrefix))) {
            $candidates[] = sprintf('App\\%s\\Controller\\Crud\\%s%sCrudController', $componentPrefix, $componentPrefix, $shortName);
            $candidates[] = sprintf('App\\%s\\Controller\\Admin\\%s%sCrudController', $componentPrefix, $componentPrefix, $shortName);
            $candidates[] = sprintf('App\\%s\\Controller\\Admin\\%sCrudController', $componentPrefix, $shortName);
            $candidates[] = sprintf('App\\%s\\Controller\\Crud\\%sCrudController', $componentPrefix, $shortName);
        }

        $candidates[] = sprintf('App\\Controller\\Crud\\%sCrudController', $shortName);
        $candidates[] = sprintf('App\\Controller\\Admin\\%sCrudController', $shortName);

        foreach (array_unique($candidates) as $candidate) {
            if (class_exists($candidate) && is_subclass_of($candidate, CrudControllerInterface::class)) {
                return $candidate;
            }
        }

        return null;
    }
}
