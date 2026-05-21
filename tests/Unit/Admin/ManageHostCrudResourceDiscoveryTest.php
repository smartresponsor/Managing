<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Service\Admin\Host\ManageHostClassNameResolver;
use App\Managing\Service\Admin\Host\ManageHostCrudControllerResolver;
use App\Managing\Service\Admin\Host\ManageHostCrudResourceCache;
use App\Managing\Service\Admin\Host\ManageHostCrudResourceDiscovery;
use App\Managing\Service\Admin\Host\ManageHostCrudResourceFactory;
use App\Managing\Service\Admin\Host\ManageHostDoctrineEntityInspector;
use App\Managing\Service\Admin\Host\ManageHostPathResolver;
use PHPUnit\Framework\TestCase;

final class ManageHostCrudResourceDiscoveryTest extends TestCase
{
    public function testDiscoversDoctrineEntityResourcesThroughDedicatedDiscoveryService(): void
    {
        $projectDir = sys_get_temp_dir().'/manage-discovery-'.bin2hex(random_bytes(4));
        $entityDir = $projectDir.'/src/Cataloging/Entity';
        $configDir = $projectDir.'/config/packages';
        mkdir($entityDir, 0777, true);
        mkdir($configDir, 0777, true);

        $entityFile = $entityDir.'/Product.php';
        file_put_contents($entityFile, <<<'PHP_SOURCE'
<?php

namespace App\Cataloging\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class Product
{
    #[ORM\Id]
    private int $id = 1;
}
PHP_SOURCE);

        file_put_contents($configDir.'/doctrine.yaml', <<<'YAML'
doctrine:
  orm:
    entity_managers:
      default:
        mappings:
          Cataloging:
            dir: '%kernel.project_dir%/src/Cataloging/Entity'
            prefix: 'App\Cataloging\Entity'
YAML);

        require_once $entityFile;

        try {
            $pathResolver = new ManageHostPathResolver($projectDir);
            $classNameResolver = new ManageHostClassNameResolver();
            $resourceFactory = new ManageHostCrudResourceFactory(
                $classNameResolver,
                new ManageHostCrudControllerResolver($classNameResolver),
            );
            $discovery = new ManageHostCrudResourceDiscovery(
                pathResolver: $pathResolver,
                classNameResolver: $classNameResolver,
                entityInspector: new ManageHostDoctrineEntityInspector($projectDir, $pathResolver),
                resourceFactory: $resourceFactory,
                resourceCache: new ManageHostCrudResourceCache($projectDir, $projectDir.'/var/cache'),
            );

            $resources = $discovery->discover();

            self::assertCount(1, $resources);
            self::assertSame('cataloging', $resources[0]->componentKey);
            self::assertSame('product', $resources[0]->resourceKey);
            self::assertSame('App\\Cataloging\\Entity\\Product', $resources[0]->entityClass);
        } finally {
            $this->removeDirectory($projectDir);
        }
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isDir()) {
                @rmdir($file->getPathname());
                continue;
            }

            if ($file instanceof \SplFileInfo) {
                @unlink($file->getPathname());
            }
        }

        @rmdir($directory);
    }
}
