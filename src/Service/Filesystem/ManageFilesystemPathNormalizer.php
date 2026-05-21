<?php

declare(strict_types=1);

namespace App\Managing\Service\Filesystem;

/**
 * Normalizes host filesystem paths without depending on newer .NET/OS helpers.
 *
 * Host discovery needs the same absolute-path and slash-normalization rules in
 * several services. Keeping those rules here avoids small divergent path
 * implementations across the scanner.
 */
final class ManageFilesystemPathNormalizer
{
    public function absolutePath(string $projectDir, string $path): string
    {
        $trimmedPath = trim($path);
        if ('' === $trimmedPath) {
            return rtrim($projectDir, '/\\');
        }

        if ($this->isAbsolutePath($trimmedPath) && !$this->looksLikeProjectRelativeRootSegment($trimmedPath)) {
            return $trimmedPath;
        }

        return rtrim($projectDir, '/\\').'/'.ltrim($trimmedPath, '/\\');
    }

    public function normalizePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }

    public function isAbsolutePath(string $path): bool
    {
        if ('' === $path) {
            return false;
        }

        if (1 === preg_match('~^[A-Za-z]:[\\\\/]~', $path)) {
            return true;
        }

        if (str_starts_with($path, '\\')) {
            return true;
        }

        return str_starts_with($path, '/');
    }

    public function realPathOrOriginal(string $path): string
    {
        return realpath($path) ?: $path;
    }

    private function looksLikeProjectRelativeRootSegment(string $path): bool
    {
        if (!str_starts_with($path, '/')) {
            return false;
        }

        $segment = ltrim($path, '/');
        if ('' === $segment || str_contains($segment, '/')) {
            return false;
        }

        return in_array($segment, ['config', 'docs', 'public', 'src', 'tests', 'tools', 'var', 'vendor'], true);
    }
}
