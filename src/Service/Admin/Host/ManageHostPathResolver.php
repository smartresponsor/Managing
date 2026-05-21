<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin\Host;

use App\Managing\Service\Filesystem\ManageFilesystemPathNormalizer;

final class ManageHostPathResolver
{
    /**
     * @param list<string> $sourceRoots
     * @param list<string> $namespacePrefixes
     * @param list<string> $excludedNamespaces
     */
    public function __construct(
        private readonly string $projectDir,
        private readonly array $sourceRoots = ['src'],
        private readonly array $namespacePrefixes = ['App\\'],
        private readonly array $excludedNamespaces = ['App\\Managing\\'],
        private ?ManageHostPsr4RootResolver $psr4RootResolver = null,
        private readonly ManageFilesystemPathNormalizer $pathNormalizer = new ManageFilesystemPathNormalizer(),
    ) {
    }

    /** @return iterable<\SplFileInfo> */
    public function findPhpFiles(string $pathNeedle): iterable
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
    public function sourceDirectories(): array
    {
        $directories = [];

        foreach ($this->sourceRoots as $root) {
            $path = $this->pathNormalizer->absolutePath($this->projectDir, $root);
            if (is_dir($path)) {
                $directories[] = $this->pathNormalizer->realPathOrOriginal($path);
            }
        }

        foreach ($this->psr4Roots() as $root) {
            if (is_dir($root['path'])) {
                $directories[] = $this->pathNormalizer->realPathOrOriginal($root['path']);
            }
        }

        sort($directories);

        return array_values(array_unique($directories));
    }

    /** @return list<array{namespace: string, path: string}> */
    public function psr4Roots(): array
    {
        return $this->psr4RootResolver()->roots();
    }

    public function absolutePath(string $path): string
    {
        return $this->pathNormalizer->absolutePath($this->projectDir, $path);
    }

    public function normalizePath(string $path): string
    {
        return $this->pathNormalizer->normalizePath($path);
    }

    public function isExcludedClass(string $className): bool
    {
        foreach ($this->excludedNamespaces as $excludedNamespace) {
            if (str_starts_with($className, $excludedNamespace)) {
                return true;
            }
        }

        return false;
    }

    private function psr4RootResolver(): ManageHostPsr4RootResolver
    {
        return $this->psr4RootResolver ??= new ManageHostPsr4RootResolver(
            projectDir: $this->projectDir,
            namespacePrefixes: $this->namespacePrefixes,
            excludedNamespaces: $this->excludedNamespaces,
            pathNormalizer: $this->pathNormalizer,
        );
    }
}
