<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin\Host;

use App\Managing\Service\Admin\ManageCrudResourcePolicy;
use App\Managing\Value\ManageCrudResourceDefinition;

final class ManageHostCrudResourceDiscovery
{
    public function __construct(
        private readonly ManageHostPathResolver $pathResolver,
        private readonly ManageHostClassNameResolver $classNameResolver,
        private readonly ManageHostDoctrineEntityInspector $entityInspector,
        private readonly ManageHostCrudResourceFactory $resourceFactory,
        private readonly ManageHostCrudResourceCache $resourceCache,
        private readonly ManageCrudResourcePolicy $resourcePolicy = new ManageCrudResourcePolicy(),
    ) {
    }

    /** @return list<ManageCrudResourceDefinition> */
    public function discover(): array
    {
        $cachedResources = $this->resourceCache->load($this->isCachedResourceStillValid(...));
        if (null !== $cachedResources) {
            return $cachedResources;
        }

        $resources = [];
        foreach ($this->pathResolver->findPhpFiles('/Entity/') as $file) {
            $resource = $this->resourceFromEntityFile($file);
            if (null === $resource) {
                continue;
            }

            $resources[$resource->componentKey.'.'.$resource->resourceKey] = $resource;
        }

        ksort($resources);

        $resources = array_values($resources);
        $this->resourceCache->store($resources);

        return $resources;
    }

    private function resourceFromEntityFile(\SplFileInfo $file): ?ManageCrudResourceDefinition
    {
        $filePath = $file->getPathname();
        if (!$this->entityInspector->isDoctrineEntityFile($filePath) || !$this->entityInspector->hasSingleDoctrineIdentifier($filePath)) {
            return null;
        }

        $className = $this->classNameResolver->classNameFromFile($file);
        if (null === $className || !$this->isEligibleEntityClass($className, $filePath)) {
            return null;
        }

        $componentKey = $this->classNameResolver->componentKeyFromClass($className);
        if (!$this->resourcePolicy->shouldIncludeDiscoveredEntity($componentKey, $className)) {
            return null;
        }

        return $this->resourceFactory->create($className);
    }

    private function isCachedResourceStillValid(string $entityClass): bool
    {
        try {
            $reflection = new \ReflectionClass($entityClass);
        } catch (\ReflectionException) {
            return false;
        }

        $filePath = $reflection->getFileName();
        if (!is_string($filePath) || '' === $filePath || !is_file($filePath)) {
            return false;
        }

        return $this->isEligibleEntityClass($entityClass, $filePath)
            && $this->entityInspector->hasSingleDoctrineIdentifier($filePath);
    }

    private function isEligibleEntityClass(string $className, string $filePath): bool
    {
        return !$this->pathResolver->isExcludedClass($className)
            && class_exists($className)
            && $this->entityInspector->isDoctrineManagedClass($className, $filePath);
    }
}
