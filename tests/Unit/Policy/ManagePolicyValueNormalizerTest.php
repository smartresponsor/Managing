<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Policy;

use App\Managing\Service\Policy\ManagePolicyValueNormalizer;
use PHPUnit\Framework\TestCase;

final class ManagePolicyValueNormalizerTest extends TestCase
{
    public function testStringListTrimsFiltersAndDeduplicatesValues(): void
    {
        $normalizer = new ManagePolicyValueNormalizer();

        self::assertSame(['Title', 'name'], $normalizer->stringList([' Title ', '', 123, 'name', 'Title']));
    }

    public function testLowercaseStringListNormalizesKeywords(): void
    {
        $normalizer = new ManagePolicyValueNormalizer();

        self::assertSame(['email', 'url'], $normalizer->lowercaseStringList([' Email ', 'URL', 'email']));
    }

    public function testNormalizedMapsKeepOnlyStringKeysAndValues(): void
    {
        $normalizer = new ManagePolicyValueNormalizer();

        self::assertSame(['category' => 'cataloging'], $normalizer->normalizedStringMap([' Category ' => ' Cataloging ', 1 => 'ignored']));
        self::assertSame(['\\MessageAdminView' => 200], $normalizer->intMap(['\\MessageAdminView' => '200', '' => 10]));
    }
}
