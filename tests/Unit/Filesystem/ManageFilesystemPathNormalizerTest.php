<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Filesystem;

use App\Managing\Service\Filesystem\ManageFilesystemPathNormalizer;
use PHPUnit\Framework\TestCase;

final class ManageFilesystemPathNormalizerTest extends TestCase
{
    public function testRelativePathsAreResolvedAgainstProjectDir(): void
    {
        $normalizer = new ManageFilesystemPathNormalizer();

        self::assertSame('/app/src', $normalizer->absolutePath('/app', 'src'));
        self::assertSame('/app/src', $normalizer->absolutePath('/app/', '/src'));
    }

    public function testUnixAndWindowsAbsolutePathsAreKept(): void
    {
        $normalizer = new ManageFilesystemPathNormalizer();

        self::assertSame('/var/app/src', $normalizer->absolutePath('/project', '/var/app/src'));
        self::assertSame('C:\\work\\app\\src', $normalizer->absolutePath('/project', 'C:\\work\\app\\src'));
    }

    public function testSlashNormalizationKeepsStableNoTrailingSlashPath(): void
    {
        $normalizer = new ManageFilesystemPathNormalizer();

        self::assertSame('C:/work/app/src', $normalizer->normalizePath('C:\\work\\app\\src\\'));
    }
}
