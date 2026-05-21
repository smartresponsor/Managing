<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin\Host;

use App\Managing\Value\ManageCrudResourceDefinition;

final class ManageHostCrudResourceCache
{
    private const CACHE_VERSION = 'v9';

    public function __construct(
        private readonly string $projectDir,
        private readonly ?string $cacheDir = null,
    ) {
    }

    /**
     * @param callable(string): bool $isResourceStillValid
     *
     * @return list<ManageCrudResourceDefinition>|null
     */
    public function load(callable $isResourceStillValid): ?array
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
            if ('' === $entityClass || !class_exists($entityClass) || !$isResourceStillValid($entityClass)) {
                return null;
            }

            $resources[] = new ManageCrudResourceDefinition(
                componentKey: (string) ($item['componentKey'] ?? ''),
                resourceKey: (string) ($item['resourceKey'] ?? ''),
                label: (string) ($item['label'] ?? ''),
                entityClass: $entityClass,
                crudControllerClass: array_key_exists('crudControllerClass', $item) ? $this->nullableString($item['crudControllerClass']) : null,
                formTypeClass: array_key_exists('formTypeClass', $item) ? $this->nullableString($item['formTypeClass']) : null,
                routeNamePattern: array_key_exists('routeNamePattern', $item) ? $this->nullableString($item['routeNamePattern']) : null,
                menuGroup: array_key_exists('menuGroup', $item) ? $this->nullableString($item['menuGroup']) : null,
                enabled: (bool) ($item['enabled'] ?? true),
                mode: (string) ($item['mode'] ?? ManageCrudResourceDefinition::MODE_EASYADMIN),
                resourcePath: array_key_exists('resourcePath', $item) ? $this->nullableString($item['resourcePath']) : null,
                identifierField: (string) ($item['identifierField'] ?? 'id'),
                surface: (string) ($item['surface'] ?? ManageCrudResourceDefinition::SURFACE_MANAGE),
                templatePrefix: (string) ($item['templatePrefix'] ?? 'crud'),
            );
        }

        return $resources;
    }

    /**
     * @param list<ManageCrudResourceDefinition> $resources
     */
    public function store(array $resources): void
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

        file_put_contents($cacheFile, '<?php return '.var_export($payload, true).';', LOCK_EX);
    }

    public function cacheFilePath(): string
    {
        $cacheDir = $this->cacheDir ?? ($this->projectDir.'/var/cache');

        return rtrim($cacheDir, '/\\').'/managing_host_crud_resources_'.self::CACHE_VERSION.'.php';
    }

    private function nullableString(mixed $value): ?string
    {
        return null === $value ? null : (string) $value;
    }
}
