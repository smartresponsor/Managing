<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Service\Admin\Host\ManageClassNameFormatter;
use App\Managing\Service\Admin\Host\ManagePhpClassNameExtractor;
use PHPUnit\Framework\TestCase;

final class ManageHostClassNameFormattingSplitTest extends TestCase
{
    public function testFormatterKeepsHostNamingContract(): void
    {
        $formatter = new ManageClassNameFormatter();

        self::assertSame('CatalogProduct', $formatter->studly('catalog_product'));
        self::assertSame('catalog_product', $formatter->slug('CatalogProduct'));
        self::assertSame('Catalog product', $formatter->humanize('catalog_product'));
        self::assertSame('Product', $formatter->shortClassName('App\\Cataloging\\Entity\\Product'));
        self::assertSame('invoice', $formatter->normalizeResourceShortName('InvoiceReadModel'));
    }

    public function testExtractorReadsNamespaceClassAndSkipsClassConstantReference(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'manage-class-');
        self::assertIsString($path);
        file_put_contents($path, <<<'PHP_SOURCE'
<?php

namespace App\Cataloging\Entity;

final class Product
{
    public const SELF_CLASS = self::class;
}
PHP_SOURCE);

        try {
            $extractor = new ManagePhpClassNameExtractor();

            self::assertSame('App\\Cataloging\\Entity\\Product', $extractor->classNameFromFile(new \SplFileInfo($path)));
        } finally {
            @unlink($path);
        }
    }
}
