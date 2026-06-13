<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Policy;

use App\Managing\Normalizer\Policy\ManagePolicyValueNormalizer;
use PHPUnit\Framework\TestCase;

final class ManagePolicyValueNormalizerTest extends TestCase
{
    public function testStringListTrimsFiltersAndDeduplicatesValues(): void
    {
        $normalizer = new ManagePolicyValueNormalizer();

        self::assertSame(['Title', 'nameEntity'], $normalizer->stringList([' Title ', '', 123, 'nameEntity', 'Title']));
    }

    public function testLowercaseStringListNormalizesKeywords(): void
    {
        $normalizer = new ManagePolicyValueNormalizer();

        self::assertSame(['email', 'url'], $normalizer->lowercaseStringList([' Email ', 'URL', 'email']));
    }

    public function testNormalizedMapsKeepOnlyStringKeysAndValues(): void
    {
        $normalizer = new ManagePolicyValueNormalizer();

        self::assertSame(['category' => 'cataloging'], $normalizer->normalizedStringMap([' CategoryEntity ' => ' Cataloging ', 1 => 'ignored']));
        self::assertSame(['\\MessageAdminViewEntity' => 200], $normalizer->intMap(['\\MessageAdminViewEntity' => '200', '' => 10]));
    }
}
