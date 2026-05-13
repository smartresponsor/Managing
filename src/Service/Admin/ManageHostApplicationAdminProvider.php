<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminProviderInterface;
use App\Managing\Value\ManageComponentDefinition;
use App\Managing\Value\ManageCrudResourceDefinition;
use App\Managing\Value\ManageFormDefinition;
use App\Managing\Value\ManageRouteDefinition;
use Symfony\Component\Routing\RouterInterface;

final class ManageHostApplicationAdminProvider implements ManageAdminProviderInterface
{
    /** @var array<string, string>|null */
    private ?array $psr4Roots = null;

    /** @var list<ManageCrudResourceDefinition>|null */
    private ?array $resources = null;

    /** @var list<ManageFormDefinition>|null */
    private ?array $forms = null;

    /** @var list<ManageRouteDefinition>|null */
    private ?array $routes = null;

    /**
     * @param list<string> $sourceRoots
     * @param list<string> $namespacePrefixes
     * @param list<string> $excludedNamespaces
     */
    public function __construct(
        private readonly string $projectDir,
        private readonly ?RouterInterface $router = null,
        private readonly bool $enabled = true,
        private readonly array $sourceRoots = ['src'],
        private readonly array $namespacePrefixes = ['App\\'],
        private readonly array $excludedNamespaces = ['App\\Managing\\'],
    ) {
    }

    public function getComponent(): ManageComponentDefinition
    {
        return new ManageComponentDefinition(
            key: 'host_application',
            label: 'Host application',
            description: 'Auto-discovered Symfony host application resources, forms and routes.',
        );
    }

    /** @return iterable<ManageCrudResourceDefinition> */
    public function getCrudResources(): iterable
    {
        foreach ($this->resources ??= $this->discoverResources() as $resource) {
            yield $resource;
        }
    }

    /** @return iterable<ManageRouteDefinition> */
    public function getRoutes(): iterable
    {
        foreach ($this->routes ??= $this->discoverRoutes() as $route) {
            yield $route;
        }
    }

    /** @return iterable<ManageFormDefinition> */
    public function getForms(): iterable
    {
        foreach ($this->forms ??= $this->discoverForms() as $form) {
            yield $form;
        }
    }

    public function getRelations(): iterable
    {
        return [];
    }

    public function getProbes(): iterable
    {
        return [];
    }

    /** @return iterable<ManageComponentDefinition> */
    public function getConfiguredComponents(): iterable
    {
        $components = [];

        foreach ($this->getCrudResources() as $resource) {
            $components[$resource->componentKey] = $this->componentLabel($resource->componentKey);
        }

        foreach ($this->getForms() as $form) {
            $components[$form->componentKey] = $this->componentLabel($form->componentKey);
        }

        foreach ($this->getRoutes() as $route) {
            $components[$route->componentKey] = $this->componentLabel($route->componentKey);
        }

        if ([] === $components) {
            return;
        }

        ksort($components);

        foreach ($components as $key => $label) {
            yield new ManageComponentDefinition(
                key: $key,
                label: $label,
                description: 'Auto-discovered from the Symfony host application.',
            );
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
            if (null === $className || $this->isExcludedClass($className)) {
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
                crudControllerClass: null,
                formTypeClass: null,
                routeNamePattern: null,
                menuGroup: 'Host application',
                enabled: true,
                mode: ManageCrudResourceDefinition::MODE_CUSTOM_ROUTE,
                resourcePath: $this->relativePath($file),
                identifierField: 'id',
                surface: 'admin',
                templatePrefix: 'crud',
            );
        }

        ksort($resources);

        return array_values($resources);
    }

    /** @return list<ManageFormDefinition> */
    private function discoverForms(): array
    {
        if (!$this->enabled) {
            return [];
        }

        $forms = [];

        foreach ($this->findPhpFiles('/Form/') as $file) {
            if (!str_ends_with($file->getFilename(), 'Type.php')) {
                continue;
            }

            $className = $this->classNameFromFile($file);
            if (null === $className || $this->isExcludedClass($className)) {
                continue;
            }

            $componentKey = $this->componentKeyFromClass($className);
            $formKey = $this->resourceKeyFromClass($className);

            $forms[$componentKey.'.'.$formKey] = new ManageFormDefinition(
                componentKey: $componentKey,
                formKey: $formKey,
                label: $this->humanize($this->shortClassName($className)),
                formTypeClass: $className,
                resourceKey: str_ends_with($formKey, '_type') ? substr($formKey, 0, -5) : null,
                description: sprintf('Auto-discovered host form at %s.', $this->relativePath($file)),
                menuGroup: 'Host application',
                enabled: true,
                surface: 'admin',
                mode: 'symfony_form',
            );
        }

        ksort($forms);

        return array_values($forms);
    }

    /** @return list<ManageRouteDefinition> */
    private function discoverRoutes(): array
    {
        if (!$this->enabled || null === $this->router) {
            return [];
        }

        $routes = [];

        foreach ($this->router->getRouteCollection()->all() as $name => $route) {
            if (str_starts_with($name, '_') || str_starts_with($name, 'manage_')) {
                continue;
            }

            $controller = $route->getDefault('_controller');
            if (is_string($controller) && $this->isExcludedController($controller)) {
                continue;
            }

            $componentKey = $this->componentKeyFromRouteName($name, is_string($controller) ? $controller : null);

            $routes[$name] = new ManageRouteDefinition(
                componentKey: $componentKey,
                routeName: $name,
                label: $this->humanize($name),
                kind: 'host_route',
                menuGroup: 'Host application',
                enabled: true,
            );
        }

        ksort($routes);

        return array_values($routes);
    }

    /** @return iterable<\SplFileInfo> */
    private function findPhpFiles(string $pathNeedle): iterable
    {
        foreach ($this->sourceDirectories() as $directory) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

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
            if (is_dir($root)) {
                $directories[] = realpath($root) ?: $root;
            }
        }

        return array_values(array_unique($directories));
    }

    private function classNameFromFile(\SplFileInfo $file): ?string
    {
        $path = str_replace('\\', '/', realpath($file->getPathname()) ?: $file->getPathname());

        foreach ($this->psr4Roots() as $namespacePrefix => $root) {
            $root = rtrim(str_replace('\\', '/', realpath($root) ?: $root), '/').'/';
            if (!str_starts_with($path, $root)) {
                continue;
            }

            $relative = substr($path, strlen($root));
            if (!str_ends_with($relative, '.php')) {
                continue;
            }

            return rtrim($namespacePrefix, '\\').'\\'.str_replace('/', '\\', substr($relative, 0, -4));
        }

        return null;
    }

    /** @return array<string, string> */
    private function psr4Roots(): array
    {
        if (null !== $this->psr4Roots) {
            return $this->psr4Roots;
        }

        $roots = [];
        $composerFile = $this->projectDir.'/composer.json';

        if (is_file($composerFile)) {
            $json = json_decode((string) file_get_contents($composerFile), true);
            $autoload = is_array($json) ? ($json['autoload']['psr-4'] ?? []) : [];

            if (is_array($autoload)) {
                foreach ($autoload as $namespace => $paths) {
                    foreach ((array) $paths as $path) {
                        if (!is_string($namespace) || !is_string($path)) {
                            continue;
                        }

                        $roots[$namespace] = $this->absolutePath($path);
                    }
                }
            }
        }

        foreach ($this->namespacePrefixes as $namespacePrefix) {
            if (!isset($roots[$namespacePrefix])) {
                $roots[$namespacePrefix] = $this->projectDir.'/src';
            }
        }

        foreach ($this->workspaceComposerFiles() as $composerFile) {
            $json = json_decode((string) file_get_contents($composerFile), true);
            $autoload = is_array($json) ? ($json['autoload']['psr-4'] ?? []) : [];
            if (!is_array($autoload)) {
                continue;
            }

            $packageDir = dirname($composerFile);
            foreach ($autoload as $namespace => $paths) {
                if (!is_string($namespace) || $this->isExcludedNamespace($namespace)) {
                    continue;
                }

                foreach ((array) $paths as $path) {
                    if (!is_string($path)) {
                        continue;
                    }

                    $absolute = $this->absolutePathFrom($packageDir, $path);
                    if (is_dir($absolute)) {
                        $roots[$namespace] = $absolute;
                    }
                }
            }
        }

        krsort($roots);

        return $this->psr4Roots = $roots;
    }

    /**
     * @return list<string>
     */
    private function workspaceComposerFiles(): array
    {
        $workspaceDir = realpath(dirname($this->projectDir));
        if (false === $workspaceDir || !is_dir($workspaceDir)) {
            return [];
        }

        $composerFiles = [];

        foreach (new \DirectoryIterator($workspaceDir) as $directory) {
            if ($directory->isDot() || !$directory->isDir()) {
                continue;
            }

            $composerFile = $directory->getPathname().DIRECTORY_SEPARATOR.'composer.json';
            if (!is_file($composerFile)) {
                continue;
            }

            $composerFiles[] = $composerFile;
        }

        sort($composerFiles);

        return $composerFiles;
    }

    private function absolutePathFrom(string $baseDir, string $path): string
    {
        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\\/]/', $path)) {
            return $path;
        }

        return rtrim($baseDir, '/\\').DIRECTORY_SEPARATOR.ltrim($path, '/\\');
    }

    private function absolutePath(string $path): string
    {
        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $path)) {
            return $path;
        }

        return $this->projectDir.'/'.ltrim($path, '/');
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

    private function isExcludedController(string $controller): bool
    {
        foreach ($this->excludedNamespaces as $excludedNamespace) {
            if (str_starts_with($controller, $excludedNamespace)) {
                return true;
            }
        }

        return false;
    }

    private function componentKeyFromClass(string $className): string
    {
        $parts = explode('\\', $className);

        if (isset($parts[1]) && !in_array($parts[1], ['Entity', 'Form', 'Controller'], true)) {
            return $this->slug($parts[1]);
        }

        return 'host_application';
    }

    private function componentKeyFromRouteName(string $routeName, ?string $controller): string
    {
        if (null !== $controller) {
            $controller = str_replace('::', '\\', $controller);
            $componentKey = $this->componentKeyFromClass($controller);
            if ('host_application' !== $componentKey) {
                return $componentKey;
            }
        }

        $prefix = strtok($routeName, '_');

        return is_string($prefix) && '' !== $prefix ? $this->slug($prefix) : 'host_application';
    }

    private function resourceKeyFromClass(string $className): string
    {
        return $this->slug($this->shortClassName($className));
    }

    private function componentLabel(string $componentKey): string
    {
        if ('host_application' === $componentKey) {
            return 'Host application';
        }

        return $this->humanize($componentKey);
    }

    private function shortClassName(string $className): string
    {
        $position = strrpos($className, '\\');

        return false === $position ? $className : substr($className, $position + 1);
    }

    private function relativePath(\SplFileInfo $file): string
    {
        $path = str_replace('\\', '/', realpath($file->getPathname()) ?: $file->getPathname());
        $root = rtrim(str_replace('\\', '/', realpath($this->projectDir) ?: $this->projectDir), '/').'/';

        return str_starts_with($path, $root) ? substr($path, strlen($root)) : $path;
    }

    private function slug(string $value): string
    {
        $value = preg_replace('/(?<!^)[A-Z]/', '_$0', $value) ?? $value;
        $value = strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '_', $value));

        return trim($value, '_') ?: 'host_application';
    }

    /** @return array<string, mixed> */
    public function getDiagnostics(): array
    {
        return [
            'enabled' => $this->enabled,
            'project_dir' => $this->projectDir,
            'workspace_dir' => dirname($this->projectDir),
            'source_directories' => $this->sourceDirectories(),
            'psr4_roots' => $this->psr4Roots(),
            'excluded_namespaces' => $this->excludedNamespaces,
            'resources' => count($this->resources ??= $this->discoverResources()),
            'forms' => count($this->forms ??= $this->discoverForms()),
            'routes' => count($this->routes ??= $this->discoverRoutes()),
        ];
    }

    private function humanize(string $value): string
    {
        $value = str_replace(['_', '-'], ' ', $value);
        $value = preg_replace('/(?<!^)[A-Z]/', ' $0', $value) ?? $value;

        return ucfirst(strtolower(trim($value))) ?: 'Host application';
    }
}
