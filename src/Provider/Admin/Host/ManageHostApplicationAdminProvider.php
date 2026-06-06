<?php

declare(strict_types=1);

namespace App\Managing\Provider\Admin\Host;

use App\Managing\Cache\Admin\Host\ManageHostCrudResourceCache;
use App\Managing\Discovery\Admin\Host\ManageHostCrudResourceDiscovery;
use App\Managing\Factory\Admin\Host\ManageHostCrudResourceFactory;
use App\Managing\Inspector\Admin\Host\ManageHostDoctrineEntityInspector;
use App\Managing\Policy\Admin\ManageCrudResourcePolicy;
use App\Managing\ProviderInterface\Admin\ManageAdminProviderInterface;
use App\Managing\Resolver\Admin\Host\ManageHostClassNameResolver;
use App\Managing\Resolver\Admin\Host\ManageHostCrudControllerResolver;
use App\Managing\Resolver\Admin\Host\ManageHostPathResolver;
use App\Managing\Value\ManageComponentDefinition;
use App\Managing\Value\ManageCrudResourceDefinition;

final class ManageHostApplicationAdminProvider implements ManageAdminProviderInterface
{
    /** @var list<ManageCrudResourceDefinition>|null */
    private ?array $cachedResources = null;

    private readonly ManageHostPathResolver $pathResolver;
    private readonly ManageHostCrudResourceDiscovery $resourceDiscovery;

    /**
     * @param list<string> $sourceRoots
     * @param list<string> $namespacePrefixes
     * @param list<string> $excludedNamespaces
     */
    public function __construct(
        private readonly string $projectDir,
        private readonly bool $enabled = true,
        private readonly array $sourceRoots = ['src'],
        private readonly array $namespacePrefixes = ['App\\'],
        private readonly array $excludedNamespaces = ['App\\Managing\\'],
        private readonly ?string $cacheDir = null,
        ?ManageHostPathResolver $pathResolver = null,
        ?ManageHostClassNameResolver $classNameResolver = null,
        ?ManageHostDoctrineEntityInspector $entityInspector = null,
        ?ManageHostCrudResourceFactory $resourceFactory = null,
        ?ManageHostCrudResourceCache $resourceCache = null,
        ?ManageCrudResourcePolicy $resourcePolicy = null,
        ?ManageHostCrudResourceDiscovery $resourceDiscovery = null,
    ) {
        $this->pathResolver = $pathResolver ?? new ManageHostPathResolver(
            projectDir: $projectDir,
            sourceRoots: $sourceRoots,
            namespacePrefixes: $namespacePrefixes,
            excludedNamespaces: $excludedNamespaces,
        );
        $resourcePolicy ??= new ManageCrudResourcePolicy();
        $classNameResolver ??= new ManageHostClassNameResolver($resourcePolicy);
        $entityInspector ??= new ManageHostDoctrineEntityInspector($projectDir, $this->pathResolver);
        $crudControllerResolver = new ManageHostCrudControllerResolver($classNameResolver);
        $resourceFactory ??= new ManageHostCrudResourceFactory($classNameResolver, $crudControllerResolver);
        $resourceCache ??= new ManageHostCrudResourceCache($projectDir, $cacheDir);
        $this->resourceDiscovery = $resourceDiscovery ?? new ManageHostCrudResourceDiscovery(
            pathResolver: $this->pathResolver,
            classNameResolver: $classNameResolver,
            entityInspector: $entityInspector,
            resourceFactory: $resourceFactory,
            resourceCache: $resourceCache,
            resourcePolicy: $resourcePolicy,
        );
    }

    public function getComponent(): ManageComponentDefinition
    {
        return new ManageComponentDefinition(
            key: 'app',
            label: 'App',
            description: 'Host application content resources published for the Manage surface.',
        );
    }

    /** @return iterable<ManageCrudResourceDefinition> */
    public function getCrudResources(): iterable
    {
        foreach ($this->discoverResources() as $resource) {
            yield $resource;
        }
    }

    /** @return array<string, mixed> */
    public function getDiagnostics(): array
    {
        return [
            'enabled' => $this->enabled,
            'project_dir' => $this->projectDir,
            'source_directories' => $this->pathResolver->sourceDirectories(),
            'psr4_roots' => $this->pathResolver->psr4Roots(),
            'excluded_namespaces' => $this->excludedNamespaces,
            'resources' => count($this->discoverResources()),
        ];
    }

    /** @return list<ManageCrudResourceDefinition> */
    private function discoverResources(): array
    {
        if (null !== $this->cachedResources) {
            return $this->cachedResources;
        }

        if (!$this->enabled) {
            return $this->cachedResources = [];
        }

        return $this->cachedResources = $this->resourceDiscovery->discover();
    }
}
