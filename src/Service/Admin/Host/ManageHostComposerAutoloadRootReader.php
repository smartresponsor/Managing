<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin\Host;

use App\Managing\Service\Filesystem\ManageFilesystemPathNormalizer;

/**
 * Reads PSR-4 roots from Composer metadata.
 *
 * This keeps file-format concerns away from the host root resolver: the reader
 * knows how Composer stores autoload maps, while the resolver decides which
 * roots are usable for Managing host discovery.
 */
final class ManageHostComposerAutoloadRootReader
{
    public function __construct(
        private readonly string $projectDir,
        private readonly ManageFilesystemPathNormalizer $pathNormalizer = new ManageFilesystemPathNormalizer(),
    ) {
    }

    /** @return list<array{namespace: string, path: string}> */
    public function composerRoots(): array
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

        return $this->rootsFromAutoloadMap($autoload, true);
    }

    /** @return list<array{namespace: string, path: string}> */
    public function runtimeRoots(): array
    {
        $autoloadFile = $this->projectDir.'/vendor/composer/autoload_psr4.php';
        if (!is_file($autoloadFile)) {
            return [];
        }

        $autoload = require $autoloadFile;
        if (!is_array($autoload)) {
            return [];
        }

        return $this->rootsFromAutoloadMap($autoload, false);
    }

    /**
     * @param array<mixed> $autoload
     *
     * @return list<array{namespace: string, path: string}>
     */
    private function rootsFromAutoloadMap(array $autoload, bool $makeRelativePathsAbsolute): array
    {
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
                    'path' => $makeRelativePathsAbsolute ? $this->pathNormalizer->absolutePath($this->projectDir, $path) : $path,
                ];
            }
        }

        return $roots;
    }
}
