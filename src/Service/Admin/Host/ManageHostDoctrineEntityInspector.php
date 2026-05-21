<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin\Host;

use Symfony\Component\Yaml\Yaml;

final class ManageHostDoctrineEntityInspector
{
    /** @var list<array{dir: string, prefix: string}>|null */
    private ?array $mappings = null;

    public function __construct(
        private readonly string $projectDir,
        private readonly ManageHostPathResolver $pathResolver,
    ) {
    }

    public function isDoctrineEntityFile(string $filePath): bool
    {
        $contents = (string) file_get_contents($filePath);

        return 1 === preg_match('/#\\[\\s*(?:ORM\\\\)?Entity\\b/', $contents);
    }

    public function hasSingleDoctrineIdentifier(string $filePath): bool
    {
        $contents = (string) file_get_contents($filePath);
        preg_match_all('/#\[\\s*(?:ORM\\\\)?Id\\b/', $contents, $matches);

        return 1 === count($matches[0]);
    }

    public function isDoctrineManagedClass(string $className, string $filePath): bool
    {
        $normalizedFilePath = $this->pathResolver->normalizePath($filePath);

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
    public function doctrineMappings(): array
    {
        if (null !== $this->mappings) {
            return $this->mappings;
        }

        $configFile = $this->projectDir.'/config/packages/doctrine.yaml';
        if (!is_file($configFile)) {
            return $this->mappings = [];
        }

        $config = Yaml::parseFile($configFile);
        $entityManagers = $config['doctrine']['orm']['entity_managers'] ?? [];
        if (!is_array($entityManagers)) {
            return $this->mappings = [];
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
                $resolvedDir = realpath($this->pathResolver->absolutePath($resolvedDir)) ?: $this->pathResolver->absolutePath($resolvedDir);
                $mappings[] = [
                    'dir' => $this->pathResolver->normalizePath($resolvedDir),
                    'prefix' => $prefix,
                ];
            }
        }

        return $this->mappings = $mappings;
    }
}
