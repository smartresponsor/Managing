<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Service\Admin\Host\ManageHostComposerAutoloadRootReader;
use PHPUnit\Framework\TestCase;

final class ManageHostComposerAutoloadRootReaderTest extends TestCase
{
    public function testComposerRootsAreReadFromComposerJson(): void
    {
        $projectDir = sys_get_temp_dir().'/manage_composer_root_reader_'.bin2hex(random_bytes(4));
        mkdir($projectDir.'/src', 0777, true);

        file_put_contents($projectDir.'/composer.json', json_encode([
            'autoload' => [
                'psr-4' => [
                    'App\\' => 'src/',
                ],
            ],
        ], JSON_THROW_ON_ERROR));

        $reader = new ManageHostComposerAutoloadRootReader($projectDir);

        self::assertSame([
            [
                'namespace' => 'App\\',
                'path' => $projectDir.'/src/',
            ],
        ], $reader->composerRoots());
    }

    public function testRuntimeRootsAreReadFromComposerRuntimeMap(): void
    {
        $projectDir = sys_get_temp_dir().'/manage_runtime_root_reader_'.bin2hex(random_bytes(4));
        mkdir($projectDir.'/vendor/composer', 0777, true);

        $runtimePath = $projectDir.'/src';
        file_put_contents(
            $projectDir.'/vendor/composer/autoload_psr4.php',
            '<?php return [\'App\\\\\' => [\''.str_replace('\\', '\\\\', $runtimePath).'\']];',
        );

        $reader = new ManageHostComposerAutoloadRootReader($projectDir);

        self::assertSame([
            [
                'namespace' => 'App\\',
                'path' => $runtimePath,
            ],
        ], $reader->runtimeRoots());
    }
}
