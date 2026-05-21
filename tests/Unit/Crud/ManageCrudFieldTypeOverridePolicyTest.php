<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Service\Crud\ManageCrudFieldTypeOverridePolicy;
use PHPUnit\Framework\TestCase;

final class ManageCrudFieldTypeOverridePolicyTest extends TestCase
{
    public function testConfiguredOverridesResolveByEntityAndWildcard(): void
    {
        $policy = new ManageCrudFieldTypeOverridePolicy([
            '*' => ['externalUrl' => 'url'],
            'App\\Cataloging\\Entity\\Catalog\\Product' => ['summary' => 'TEXTAREA'],
        ]);

        self::assertSame('url', $policy->explicitFieldType('App\\Other\\Entity\\Record', 'externalUrl'));
        self::assertSame('textarea', $policy->explicitFieldType('App\\Cataloging\\Entity\\Catalog\\Product', 'summary'));
    }

    public function testRuntimeOverridesWinOverConfiguredOverrides(): void
    {
        $policy = new ManageCrudFieldTypeOverridePolicy([
            'App\\Cataloging\\Entity\\Catalog\\Product' => ['summary' => 'text'],
        ]);

        self::assertSame('textarea', $policy->explicitFieldType(
            'App\\Cataloging\\Entity\\Catalog\\Product',
            'summary',
            ['summary' => 'textarea'],
        ));
    }

    public function testInvalidFieldTypesAreRejected(): void
    {
        $policy = new ManageCrudFieldTypeOverridePolicy([
            '*' => ['rawPayload' => 'invalid'],
        ]);

        self::assertNull($policy->explicitFieldType('App\\Other\\Entity\\Record', 'rawPayload'));
        self::assertNull($policy->explicitFieldType('App\\Other\\Entity\\Record', 'rawPayload', ['rawPayload' => 'invalid']));
    }
}
