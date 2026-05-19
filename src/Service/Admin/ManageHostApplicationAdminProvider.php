<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminProviderInterface;
use App\Managing\Value\ManageComponentDefinition;
use App\Managing\Value\ManageCrudResourceDefinition;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Controller\CrudControllerInterface;
use Symfony\Component\Yaml\Yaml;

final class ManageHostApplicationAdminProvider implements ManageAdminProviderInterface
{
    private const CACHE_VERSION = 'v8';

    /** @var list<ManageCrudResourceDefinition>|null */
    private ?array $cachedResources = null;

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
    ) {
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

    /** @return list<ManageCrudResourceDefinition> */
    private function discoverResources(): array
    {
        if (null !== $this->cachedResources) {
            return $this->cachedResources;
        }

        if (!$this->enabled) {
            return $this->cachedResources = [];
        }

        $cachedResources = $this->loadCachedResources();
        if (null !== $cachedResources) {
            return $this->cachedResources = $cachedResources;
        }

        $resources = [];

        foreach ($this->findPhpFiles('/src/Entity/') as $file) {
            if (!$this->isDoctrineEntityFile($file->getPathname())) {
                continue;
            }
            if (!$this->hasSingleDoctrineIdentifier($file->getPathname())) {
                continue;
            }

            $className = $this->classNameFromFile($file);
            if (
                null === $className
                || $this->isExcludedClass($className)
                || !class_exists($className)
                || !$this->isDoctrineManagedClass($className, $file->getPathname())
            ) {
                continue;
            }

            $componentKey = $this->componentKeyFromClass($className);
            if ($this->isExcludedManageResource($componentKey, $className)) {
                continue;
            }
            $resourceKey = $this->resourceKeyFromClass($className);
            $shortName = $this->shortClassName($className);

            $resources[$componentKey.'.'.$resourceKey] = new ManageCrudResourceDefinition(
                componentKey: $componentKey,
                resourceKey: $resourceKey,
                label: $this->humanize($shortName),
                entityClass: $className,
                crudControllerClass: $this->discoverCrudControllerClass($className),
                formTypeClass: null,
                routeNamePattern: null,
                menuGroup: $this->componentLabel($componentKey),
                enabled: true,
                mode: ManageCrudResourceDefinition::MODE_EASYADMIN,
                resourcePath: sprintf('%s/%s', $componentKey, $this->resourcePathSegmentFromClass($className)),
                identifierField: 'id',
                surface: 'manage',
                templatePrefix: 'crud',
            );
        }

        ksort($resources);

        $resources = array_values($resources);
        $this->storeCachedResources($resources);

        return $this->cachedResources = $resources;
    }

    /**
     * @return list<ManageCrudResourceDefinition>|null
     */
    private function loadCachedResources(): ?array
    {
        $cacheFile = $this->cacheFilePath();
        if (!is_file($cacheFile)) {
            return null;
        }

        $payload = require $cacheFile;
        if (!is_array($payload) || !isset($payload['resources']) || !is_array($payload['resources'])) {
            return null;
        }

        $resources = [];
        foreach ($payload['resources'] as $item) {
            if (!is_array($item)) {
                return null;
            }

            $entityClass = (string) ($item['entityClass'] ?? '');
            if ('' === $entityClass || !class_exists($entityClass)) {
                return null;
            }
            if (!$this->isCachedResourceStillValid($entityClass)) {
                return null;
            }

            $resources[] = new ManageCrudResourceDefinition(
                componentKey: (string) ($item['componentKey'] ?? ''),
                resourceKey: (string) ($item['resourceKey'] ?? ''),
                label: (string) ($item['label'] ?? ''),
                entityClass: $entityClass,
                crudControllerClass: isset($item['crudControllerClass']) ? $this->nullableString($item['crudControllerClass']) : null,
                formTypeClass: isset($item['formTypeClass']) ? $this->nullableString($item['formTypeClass']) : null,
                routeNamePattern: isset($item['routeNamePattern']) ? $this->nullableString($item['routeNamePattern']) : null,
                menuGroup: isset($item['menuGroup']) ? $this->nullableString($item['menuGroup']) : null,
                enabled: (bool) ($item['enabled'] ?? true),
                mode: (string) ($item['mode'] ?? ManageCrudResourceDefinition::MODE_EASYADMIN),
                resourcePath: isset($item['resourcePath']) ? $this->nullableString($item['resourcePath']) : null,
                identifierField: (string) ($item['identifierField'] ?? 'id'),
                surface: (string) ($item['surface'] ?? ManageCrudResourceDefinition::SURFACE_MANAGE),
                templatePrefix: (string) ($item['templatePrefix'] ?? 'crud'),
            );
        }

        return $resources;
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

        if ($this->isExcludedClass($entityClass) || !$this->isDoctrineManagedClass($entityClass, $filePath)) {
            return false;
        }

        return $this->hasSingleDoctrineIdentifier($filePath);
    }

    /**
     * @param list<ManageCrudResourceDefinition> $resources
     */
    private function storeCachedResources(array $resources): void
    {
        $cacheFile = $this->cacheFilePath();
        $cacheDir = dirname($cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $payload = [
            'resources' => array_map(
                static fn (ManageCrudResourceDefinition $resource): array => [
                    'componentKey' => $resource->componentKey,
                    'resourceKey' => $resource->resourceKey,
                    'label' => $resource->label,
                    'entityClass' => $resource->entityClass,
                    'crudControllerClass' => $resource->crudControllerClass,
                    'formTypeClass' => $resource->formTypeClass,
                    'routeNamePattern' => $resource->routeNamePattern,
                    'menuGroup' => $resource->menuGroup,
                    'enabled' => $resource->enabled,
                    'mode' => $resource->mode,
                    'resourcePath' => $resource->resourcePath,
                    'identifierField' => $resource->identifierField,
                    'surface' => $resource->surface,
                    'templatePrefix' => $resource->templatePrefix,
                ],
                $resources,
            ),
        ];

        $content = '<?php return '.var_export($payload, true).';';
        file_put_contents($cacheFile, $content, LOCK_EX);
    }

    private function cacheFilePath(): string
    {
        $cacheDir = $this->cacheDir ?? ($this->projectDir.'/var/cache');

        return rtrim($cacheDir, '/\\').'/managing_host_crud_resources_'.self::CACHE_VERSION.'.php';
    }

    private function nullableString(mixed $value): ?string
    {
        return null === $value ? null : (string) $value;
    }

    /** @return iterable<\SplFileInfo> */
    private function findPhpFiles(string $pathNeedle): iterable
    {
        foreach ($this->sourceDirectories() as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile() || 'php' !== $file->getExtension()) {
                    continue;
                }

                $path = str_replace('\\', '/', $file->getPathname());
                if (!str_contains($path, $pathNeedle)) {
                    continue;
                }

                yield $file;
            }
        }
    }

    /** @return list<string> */
    private function sourceDirectories(): array
    {
        $directories = [];

        foreach ($this->sourceRoots as $root) {
            $path = $this->absolutePath($root);
            if (is_dir($path)) {
                $directories[] = realpath($path) ?: $path;
            }
        }

        foreach ($this->psr4Roots() as $root) {
            if (is_dir($root['path'])) {
                $directories[] = realpath($root['path']) ?: $root['path'];
            }
        }

        sort($directories);

        return array_values(array_unique($directories));
    }

    private function classNameFromFile(\SplFileInfo $file): ?string
    {
        $tokens = token_get_all((string) file_get_contents($file->getPathname()));
        $namespace = '';

        for ($index = 0, $count = count($tokens); $index < $count; ++$index) {
            $token = $tokens[$index];
            if (!is_array($token) || T_NAMESPACE !== $token[0]) {
                continue;
            }

            for (++$index; $index < $count; ++$index) {
                $part = $tokens[$index];
                if (is_array($part) && in_array($part[0], [T_STRING, T_NAME_QUALIFIED], true)) {
                    $namespace .= $part[1];
                    continue;
                }
                if ('\\' === $part) {
                    $namespace .= '\\';
                    continue;
                }
                if (';' === $part || '{' === $part) {
                    break;
                }
            }

            break;
        }

        for ($index = 0, $count = count($tokens); $index < $count; ++$index) {
            $token = $tokens[$index];
            if (!is_array($token) || T_CLASS !== $token[0]) {
                continue;
            }

            $previousIndex = $index - 1;
            while ($previousIndex >= 0 && is_array($tokens[$previousIndex]) && T_WHITESPACE === $tokens[$previousIndex][0]) {
                --$previousIndex;
            }
            if ($previousIndex >= 0 && is_array($tokens[$previousIndex]) && T_DOUBLE_COLON === $tokens[$previousIndex][0]) {
                continue;
            }

            for (++$index; $index < $count; ++$index) {
                $part = $tokens[$index];
                if (is_array($part) && T_STRING === $part[0]) {
                    return '' === $namespace ? $part[1] : $namespace.'\\'.$part[1];
                }
            }
        }

        return null;
    }

    /** @return list<array{namespace: string, path: string}> */
    private function psr4Roots(): array
    {
        $roots = [...$this->composerPsr4Roots(), ...$this->runtimePsr4Roots()];

        foreach ($this->namespacePrefixes as $namespacePrefix) {
            if (!$this->containsNamespaceRoot($roots, $namespacePrefix)) {
                $roots[] = [
                    'namespace' => $namespacePrefix,
                    'path' => $this->projectDir.'/src',
                ];
            }
        }

        $unique = [];
        foreach ($roots as $root) {
            if ($this->isExcludedNamespace($root['namespace']) || !is_dir($root['path'])) {
                continue;
            }

            $path = realpath($root['path']) ?: $root['path'];
            $unique[$root['namespace'].'|'.$path] = [
                'namespace' => $root['namespace'],
                'path' => $path,
            ];
        }

        ksort($unique);

        return array_values($unique);
    }

    /** @return list<array{namespace: string, path: string}> */
    private function composerPsr4Roots(): array
    {
        $composerFile = $this->projectDir.'/composer.json';
        if (!is_file($composerFile)) {
            return [];
        }

        $json = json_decode((string) file_get_contents($composerFile), true);
        $autoload = is_array($json) ? ($json['autoload']['psr-4'] ?? []) : [];
        if (!is_array($autoload)) {
            return [];
        }

        $roots = [];
        foreach ($autoload as $namespace => $paths) {
            if (!is_string($namespace)) {
                continue;
            }

            foreach ((array) $paths as $path) {
                if (!is_string($path)) {
                    continue;
                }

                $roots[] = [
                    'namespace' => $namespace,
                    'path' => $this->absolutePath($path),
                ];
            }
        }

        return $roots;
    }

    /** @return list<array{namespace: string, path: string}> */
    private function runtimePsr4Roots(): array
    {
        $autoloadFile = $this->projectDir.'/vendor/composer/autoload_psr4.php';
        if (!is_file($autoloadFile)) {
            return [];
        }

        $autoload = require $autoloadFile;
        if (!is_array($autoload)) {
            return [];
        }

        $roots = [];
        foreach ($autoload as $namespace => $paths) {
            if (!is_string($namespace)) {
                continue;
            }

            foreach ((array) $paths as $path) {
                if (!is_string($path)) {
                    continue;
                }

                $roots[] = [
                    'namespace' => $namespace,
                    'path' => $path,
                ];
            }
        }

        return $roots;
    }

    /**
     * @param list<array{namespace: string, path: string}> $roots
     */
    private function containsNamespaceRoot(array $roots, string $namespacePrefix): bool
    {
        foreach ($roots as $root) {
            if ($root['namespace'] === $namespacePrefix) {
                return true;
            }
        }

        return false;
    }

    private function isDoctrineEntityFile(string $filePath): bool
    {
        $contents = (string) file_get_contents($filePath);

        return 1 === preg_match('/#\\[\\s*(?:ORM\\\\)?Entity\\b/', $contents);
    }

    private function absolutePath(string $path): string
    {
        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $path)) {
            return $path;
        }

        return $this->projectDir.'/'.ltrim($path, '/\\');
    }

    private function isExcludedClass(string $className): bool
    {
        foreach ($this->excludedNamespaces as $excludedNamespace) {
            if (str_starts_with($className, $excludedNamespace)) {
                return true;
            }
        }

        return false;
    }

    private function isExcludedManageResource(string $componentKey, string $className): bool
    {
        if ('tagging' !== $componentKey) {
            return false;
        }

        return !str_ends_with($className, '\\TagAdminView');
    }

    private function isExcludedNamespace(string $namespace): bool
    {
        foreach ($this->excludedNamespaces as $excludedNamespace) {
            if (str_starts_with($namespace, $excludedNamespace)) {
                return true;
            }
        }

        return false;
    }

    private function hasSingleDoctrineIdentifier(string $filePath): bool
    {
        $contents = (string) file_get_contents($filePath);
        preg_match_all('/#\[\\s*(?:ORM\\\\)?Id\\b/', $contents, $matches);

        return 1 === count($matches[0]);
    }

    private function isDoctrineManagedClass(string $className, string $filePath): bool
    {
        $normalizedFilePath = $this->normalizePath($filePath);

        foreach ($this->doctrineMappings() as $mapping) {
            if (!str_starts_with($className, $mapping['prefix'])) {
                continue;
            }

            if ($normalizedFilePath === $mapping['dir'] || str_starts_with($normalizedFilePath, $mapping['dir'].'/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<array{dir: string, prefix: string}>
     */
    private function doctrineMappings(): array
    {
        static $mappings = null;
        if (null !== $mappings) {
            return $mappings;
        }

        $configFile = $this->projectDir.'/config/packages/doctrine.yaml';
        if (!is_file($configFile)) {
            return $mappings = [];
        }

        $config = Yaml::parseFile($configFile);
        $entityManagers = $config['doctrine']['orm']['entity_managers'] ?? [];
        if (!is_array($entityManagers)) {
            return $mappings = [];
        }

        $mappings = [];
        foreach ($entityManagers as $entityManagerConfig) {
            if (!is_array($entityManagerConfig)) {
                continue;
            }

            $entityManagerMappings = $entityManagerConfig['mappings'] ?? [];
            if (!is_array($entityManagerMappings)) {
                continue;
            }

            foreach ($entityManagerMappings as $mapping) {
                if (!is_array($mapping)) {
                    continue;
                }

                $dir = $mapping['dir'] ?? null;
                $prefix = $mapping['prefix'] ?? null;
                if (!is_string($dir) || '' === $dir || !is_string($prefix) || '' === $prefix) {
                    continue;
                }

                $resolvedDir = str_replace('%kernel.project_dir%', $this->projectDir, $dir);
                $resolvedDir = realpath($this->absolutePath($resolvedDir)) ?: $this->absolutePath($resolvedDir);
                $mappings[] = [
                    'dir' => $this->normalizePath($resolvedDir),
                    'prefix' => $prefix,
                ];
            }
        }

        return $mappings;
    }

    private function normalizePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }

    private function componentKeyFromClass(string $className): string
    {
        $parts = explode('\\', $className);

        if (isset($parts[1]) && !in_array($parts[1], ['Entity', 'Controller'], true)) {
            return $this->slug($parts[1]);
        }

        if (isset($parts[2])) {
            $rootNamespaceComponentMap = [
                'Attachment' => 'attaching',
                'Billing' => 'billing',
                'Catalog' => 'cataloging',
                'Category' => 'cataloging',
                'Currency' => 'currencing',
                'Exchange' => 'exchanging',
                'Message' => 'messaging',
                'Order' => 'ordering',
                'Page' => 'paging',
                'Payment' => 'paying',
                'Shipment' => 'shipping',
                'Tag' => 'tagging',
                'Tax' => 'taxating',
                'Vendor' => 'vendoring',
            ];

            if (isset($rootNamespaceComponentMap[$parts[2]])) {
                return $rootNamespaceComponentMap[$parts[2]];
            }

            return $this->slug($parts[2]);
        }

        return 'app';
    }

    private function discoverCrudControllerClass(string $entityClass): ?string
    {
        $parts = explode('\\', $entityClass);
        $shortName = $this->shortClassName($entityClass);
        $componentPrefix = isset($parts[1]) && !in_array($parts[1], ['Entity', 'Form', 'Controller'], true) ? $parts[1] : '';
        $componentKey = $this->componentKeyFromClass($entityClass);

        $candidates = [];
        $candidates[] = sprintf('App\\Managing\\Controller\\Crud\\Generated\\%sCrudController', $this->studly($componentKey));

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

    private function resourceKeyFromClass(string $className): string
    {
        return $this->slug($this->shortClassName($className));
    }

    private function resourcePathSegmentFromClass(string $className): string
    {
        $shortName = $this->shortClassName($className);
        $shortName = preg_replace('/Entity$/', '', $shortName) ?? $shortName;

        return $this->slug($shortName);
    }

    private function componentLabel(string $componentKey): string
    {
        if ('app' === $componentKey) {
            return 'App';
        }

        return $this->humanize($componentKey);
    }

    private function shortClassName(string $className): string
    {
        $position = strrpos($className, '\\');

        return false === $position ? $className : substr($className, $position + 1);
    }

    private function studly(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9]+/', ' ', $value) ?? $value;
        $value = ucwords(strtolower(trim($value)));

        return str_replace(' ', '', $value);
    }

    private function slug(string $value): string
    {
        $value = preg_replace('/(?<!^)[A-Z]/', '_$0', $value) ?? $value;
        $value = strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '_', $value));

        return trim($value, '_') ?: 'app';
    }

    /** @return array<string, mixed> */
    public function getDiagnostics(): array
    {
        return [
            'enabled' => $this->enabled,
            'project_dir' => $this->projectDir,
            'source_directories' => $this->sourceDirectories(),
            'psr4_roots' => $this->psr4Roots(),
            'excluded_namespaces' => $this->excludedNamespaces,
            'resources' => count($this->discoverResources()),
        ];
    }

    private function humanize(string $value): string
    {
        $value = str_replace(['_', '-'], ' ', $value);
        $value = preg_replace('/(?<!^)[A-Z]/', ' $0', $value) ?? $value;

        return ucfirst(strtolower(trim($value))) ?: 'Host application';
    }
}
