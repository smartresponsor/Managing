<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin\Host;

use App\Managing\Service\Filesystem\ManageFilesystemPathNormalizer;

/**
 * Resolves host PSR-4 roots from composer metadata.
 *
 * Keeping Composer/runtime autoload parsing outside ManageHostPathResolver
 * makes the host scanner easier to reason about: one service reads roots,
 * another service scans paths.
 */
final class ManageHostPsr4RootResolver
{
    /**
     * @param list<string> $namespacePrefixes
     * @param list<string> $excludedNamespaces
     */
    public function __construct(
        private readonly string $projectDir,
        private readonly array $namespacePrefixes = ['App\\'],
        private readonly array $excludedNamespaces = ['App\\Managing\\'],
        private readonly ManageFilesystemPathNormalizer $pathNormalizer = new ManageFilesystemPathNormalizer(),
        private ?ManageHostComposerAutoloadRootReader $autoloadRootReader = null,
    ) {
    }

    /** @return list<array{namespace: string, path: string}> */
    public function roots(): array
    {
        $autoloadRootReader = $this->autoloadRootReader();
        $roots = [...$autoloadRootReader->composerRoots(), ...$autoloadRootReader->runtimeRoots()];

        foreach ($this->namespacePrefixes as $namespacePrefix) {
            if (!$this->containsNamespaceRoot($roots, $namespacePrefix)) {
                $roots[] = [
                    'namespace' => $namespacePrefix,
                    'path' => $this->pathNormalizer->absolutePath($this->projectDir, 'src'),
                ];
            }
        }

        return $this->uniqueExistingRoots($roots);
    }

    /**
     * @param list<array{namespace: string, path: string}> $roots
     *
     * @return list<array{namespace: string, path: string}>
     */
    private function uniqueExistingRoots(array $roots): array
    {
        $unique = [];
        foreach ($roots as $root) {
            if ($this->isExcludedNamespace($root['namespace']) || !is_dir($root['path'])) {
                continue;
            }

            $path = $this->pathNormalizer->realPathOrOriginal($root['path']);
            $unique[$root['namespace'].'|'.$path] = [
                'namespace' => $root['namespace'],
                'path' => $path,
            ];
        }

        ksort($unique);

        return array_values($unique);
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

    private function autoloadRootReader(): ManageHostComposerAutoloadRootReader
    {
        return $this->autoloadRootReader ??= new ManageHostComposerAutoloadRootReader(
            projectDir: $this->projectDir,
            pathNormalizer: $this->pathNormalizer,
        );
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
}
