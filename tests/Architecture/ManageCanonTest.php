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

    public function testManagingDoesNotDependOnInterfacingOrBridging(): void
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

            if (str_contains($contents, 'Interfacing') || str_contains($contents, 'Bridging')) {
                $violations[] = str_replace(dirname(__DIR__, 2).'/', '', $file->getPathname());
            }
        }

        self::assertSame([], $violations);
    }

    public function testManageRouteIsCanonicalAdminMountPoint(): void
    {
        $dashboard = file_get_contents(dirname(__DIR__, 2).'/src/Controller/Admin/ManageDashboardController.php');

        self::assertIsString($dashboard);
        self::assertStringContainsString("#[AdminDashboard(routePath: '/manage', routeName: 'manage_dashboard')]", $dashboard);
        self::assertStringNotContainsString("#[Route('/admin'", $dashboard);
    }
}
