<?php

declare(strict_types=1);

namespace App\Managing\Tests\Architecture;

use PHPUnit\Framework\TestCase;

final class ManageCanonTest extends TestCase
{
    public function testNoDomainDirectoryExists(): void
    {
        self::assertDirectoryDoesNotExist(dirname(__DIR__, 2).'/src/Domain');
    }

    public function testPhpClassesUseManagingNamespace(): void
    {
        $violations = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(dirname(__DIR__, 2).'/src'));

        foreach ($iterator as $file) {
            if (!$file->isFile() || 'php' !== $file->getExtension()) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            if (!is_string($contents) || !str_contains($contents, 'namespace App\\Managing')) {
                $violations[] = str_replace(dirname(__DIR__, 2).'/', '', $file->getPathname());
            }
        }

        self::assertSame([], $violations);
    }

    public function testManagingDoesNotReferenceDeletedSystemLayers(): void
    {
        $violations = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(dirname(__DIR__, 2).'/src'));

        foreach ($iterator as $file) {
            if (!$file->isFile() || 'php' !== $file->getExtension()) {
                continue;
            }

            $contents = file_get_contents($file->getPathname());
            if (!is_string($contents)) {
                continue;
            }

            if (
                str_contains($contents, 'ManageSelfAdminProvider')
                || str_contains($contents, 'ManageConfiguredAdminProvider')
                || str_contains($contents, 'ManageRouteDefinition')
                || str_contains($contents, 'ManageFormDefinition')
                || str_contains($contents, 'ManageProbeDefinition')
                || str_contains($contents, 'ManageRelationDefinition')
                || str_contains($contents, 'ManageProbeResult')
                || str_contains($contents, 'ManageCrudingRouteBridge')
            ) {
                $violations[] = str_replace(dirname(__DIR__, 2).'/', '', $file->getPathname());
            }
        }

        self::assertSame([], $violations);
    }

    public function testManageRouteIsCanonicalAdminMountPoint(): void
    {
        $dashboard = file_get_contents(dirname(__DIR__, 2).'/src/Controller/Admin/ManageDashboardController.php');

        self::assertIsString($dashboard);
        self::assertStringContainsString("#[AdminDashboard(routePath: '/manage', routeName: 'manage')]", $dashboard);
        self::assertStringNotContainsString("#[Route('/admin'", $dashboard);
        self::assertStringNotContainsString('function component(', $dashboard);
        self::assertStringNotContainsString('routeName: \'component\'', $dashboard);
    }
}
