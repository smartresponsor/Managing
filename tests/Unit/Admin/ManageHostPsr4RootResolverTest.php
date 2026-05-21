<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Service\Admin\Host\ManageHostPsr4RootResolver;
use PHPUnit\Framework\TestCase;

final class ManageHostPsr4RootResolverTest extends TestCase
{
    public function testComposerRootsAreResolvedAndExcludedNamespacesAreIgnored(): void
    {
        $projectDir = sys_get_temp_dir().'/manage_psr4_roots_'.bin2hex(random_bytes(4));
        mkdir($projectDir.'/src/Feature', 0777, true);
        mkdir($projectDir.'/src/Managing', 0777, true);

        file_put_contents($projectDir.'/composer.json', json_encode([
            'autoload' => [
                'psr-4' => [
                    'App\\' => 'src/',
                    'App\\Managing\\' => 'src/Managing/',
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $resolver = new ManageHostPsr4RootResolver(
            projectDir: $projectDir,
            namespacePrefixes: ['App\\'],
            excludedNamespaces: ['App\\Managing\\'],
        );

        $roots = $resolver->roots();

        self::assertSame('App\\', $roots[0]['namespace']);
        self::assertSame(realpath($projectDir.'/src'), $roots[0]['path']);
        self::assertCount(1, $roots);
    }

    public function testConfiguredNamespacePrefixGetsDefaultSrcRootWhenComposerFileIsMissing(): void
    {
        $projectDir = sys_get_temp_dir().'/manage_psr4_default_'.bin2hex(random_bytes(4));
        mkdir($projectDir.'/src', 0777, true);

        $resolver = new ManageHostPsr4RootResolver($projectDir, ['App\\'], []);

        self::assertSame([
            [
                'namespace' => 'App\\',
                'path' => realpath($projectDir.'/src'),
            ],
        ], $resolver->roots());
    }

    public function testRelativeComposerRootUsesSharedPathNormalization(): void
    {
        $projectDir = sys_get_temp_dir().'/manage_psr4_relative_'.bin2hex(random_bytes(4));
        mkdir($projectDir.'/packages/Module/src', 0777, true);

        file_put_contents($projectDir.'/composer.json', json_encode([
            'autoload' => [
                'psr-4' => [
                    'Vendor\\Module\\' => 'packages/Module/src/',
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $resolver = new ManageHostPsr4RootResolver(
            projectDir: $projectDir,
            namespacePrefixes: ['Vendor\\Module\\'],
            excludedNamespaces: [],
        );

        self::assertSame([
            [
                'namespace' => 'Vendor\\Module\\',
                'path' => realpath($projectDir.'/packages/Module/src'),
            ],
        ], $resolver->roots());
    }
}
