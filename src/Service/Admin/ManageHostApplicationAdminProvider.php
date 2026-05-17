<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminProviderInterface;
use App\Managing\Value\ManageComponentDefinition;
use App\Managing\Value\ManageCrudResourceDefinition;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Controller\CrudControllerInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class ManageHostApplicationAdminProvider implements ManageAdminProviderInterface
{
    /** @var list<array{namespace: string, path: string}>|null */
    private ?array $psr4Roots = null;

    /** @var list<ManageCrudResourceDefinition>|null */
    private ?array $resources = null;

    /**
     * @param list<string> $sourceRoots
     * @param list<string> $namespacePrefixes
     * @param list<string> $excludedNamespaces
     */
    public function __construct(
        private readonly string $projectDir,
        private readonly CacheInterface $cache,
        private readonly bool $enabled = true,
        private readonly array $sourceRoots = ['src'],
        private readonly array $namespacePrefixes = ['App\\'],
        private readonly array $excludedNamespaces = ['App\\Managing\\'],
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
        foreach ($this->resources ??= $this->cache->get($this->resourcesCacheKey(), fn (): array => $this->discoverResources()) as $resource) {
            yield $resource;
        }
    }

    /** @return list<ManageCrudResourceDefinition> */
    private function discoverResources(): array
    {
        if (!$this->enabled) {
            return [];
        }

        $resources = [];

        foreach ($this->findPhpFiles('/Entity/') as $file) {
            $className = $this->classNameFromFile($file);
            if (null === $className || $this->isExcludedClass($className) || !class_exists($className)) {
                continue;
            }

            $componentKey = $this->componentKeyFromClass($className);
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

        return array_values($resources);
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
        if (null !== $this->psr4Roots) {
            return $this->psr4Roots;
        }

        return $this->psr4Roots = $this->cache->get($this->psr4RootsCacheKey(), function (): array {
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
        });
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

    private function isExcludedNamespace(string $namespace): bool
    {
        foreach ($this->excludedNamespaces as $excludedNamespace) {
            if (str_starts_with($namespace, $excludedNamespace)) {
                return true;
            }
        }

        return false;
    }

    private function componentKeyFromClass(string $className): string
    {
        $parts = explode('\\', $className);

        if (isset($parts[1]) && !in_array($parts[1], ['Entity', 'Controller'], true)) {
            return $this->slug($parts[1]);
        }

        return 'app';
    }

    private function discoverCrudControllerClass(string $entityClass): ?string
    {
        $parts = explode('\\', $entityClass);
        $shortName = $this->shortClassName($entityClass);
        $componentPrefix = isset($parts[1]) && !in_array($parts[1], ['Entity', 'Form', 'Controller'], true) ? $parts[1] : '';

        $candidates = [];
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
            'resources' => count($this->resources ??= $this->discoverResources()),
        ];
    }

    private function resourcesCacheKey(): string
    {
        return 'managing.host_app.crud_resources.'.$this->metadataSignature();
    }

    private function psr4RootsCacheKey(): string
    {
        return 'managing.host_app.psr4_roots.'.$this->metadataSignature();
    }

    private function metadataSignature(): string
    {
        $files = [
            $this->projectDir.'/composer.json',
            $this->projectDir.'/vendor/composer/autoload_psr4.php',
        ];

        $parts = [];
        foreach ($files as $file) {
            $parts[] = is_file($file) ? $file.':'.((string) filemtime($file)).':'.((string) filesize($file)) : $file.':missing';
        }

        return substr(hash('sha256', implode('|', $parts)), 0, 16);
    }

    private function humanize(string $value): string
    {
        $value = str_replace(['_', '-'], ' ', $value);
        $value = preg_replace('/(?<!^)[A-Z]/', ' $0', $value) ?? $value;

        return ucfirst(strtolower(trim($value))) ?: 'Host application';
    }
}
