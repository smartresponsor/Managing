<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\DependencyInjection;

use App\Managing\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ManagingConfigurationNodeBuilderTest extends TestCase
{
    public function testConfigurationTreeStillProcessesCoreDefaults(): void
    {
        $processor = new Processor();
        $processed = $processor->processConfiguration(new Configuration(), []);

        self::assertSame('/manage', $processed['admin_route_prefix']);
        self::assertTrue($processed['host_scan_enabled']);
        self::assertContains('firstTitle', $processed['crud_behavior_search_fields']);
        self::assertSame('cataloging', $processed['component_root_aliases']['category']);
    }

    public function testConfigurationTreeStillProcessesNestedOverrides(): void
    {
        $processor = new Processor();
        $processed = $processor->processConfiguration(new Configuration(), [[
            'crud_field_type_overrides' => [
                'App\\Cataloging\\Entity\\CatalogProduct' => [
                    'publicUrl' => 'url',
                ],
            ],
        ]]);

        self::assertSame(
            'url',
            $processed['crud_field_type_overrides']['App\\Cataloging\\Entity\\CatalogProduct']['publicUrl']
        );
    }
}
