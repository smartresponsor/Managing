<?php

declare(strict_types=1);

namespace App\Managing\Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ManageRcCleanArchitectureTest extends TestCase
{
    public function testProductionClassesStayBelowRcCleanLineBudget(): void
    {
        $violations = [];
        $root = dirname(__DIR__, 2);
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root.'/src'));

        foreach ($iterator as $file) {
            if (!$file->isFile() || 'php' !== $file->getExtension()) {
                continue;
            }

            $relativePath = str_replace($root.'/', '', $file->getPathname());
            $lineCount = substr_count((string) file_get_contents($file->getPathname()), "\n") + 1;

            if ($lineCount > 180) {
                $violations[] = sprintf('%s has %d lines', $relativePath, $lineCount);
            }
        }

        self::assertSame([], $violations);
    }

    public function testGenericManagingLayerDoesNotHardcodeNeighborComponentClasses(): void
    {
        $violations = [];
        $root = dirname(__DIR__, 2);
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root.'/src'));
        $forbiddenNeedles = [
            'App\\Applicating\\',
            'App\\Attaching\\',
            'App\\Cataloging\\',
            'App\\Messaging\\',
            'App\\Tagging\\',
            'ROLE_APPLICATION_',
        ];

        foreach ($iterator as $file) {
            if (!$file->isFile() || 'php' !== $file->getExtension()) {
                continue;
            }

            $relativePath = str_replace('\\', '/', str_replace($root.'/', '', $file->getPathname()));
            if (str_contains($relativePath, 'src/Controller/Crud/Generated/')) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            if (!is_string($contents)) {
                continue;
            }

            foreach ($forbiddenNeedles as $needle) {
                if (str_contains($contents, $needle)) {
                    $violations[] = sprintf('%s contains %s', $relativePath, $needle);
                }
            }
        }

        self::assertSame([], $violations);
    }

    public function testRcReportsExistForClosingCleanWaves(): void
    {
        $root = dirname(__DIR__, 2);

        foreach ([33, 34, 35, 36] as $wave) {
            self::assertFileExists($root.sprintf('/delivery/rc/managing_rc_clean_wave%d.md', $wave));
        }
    }
}
