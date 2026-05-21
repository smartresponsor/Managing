<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Service\Crud\ManageCrudFieldPolicy;
use PHPUnit\Framework\TestCase;

final class ManageCrudFieldPolicyTest extends TestCase
{
    public function testFieldDiscoveryVocabularyIsPolicyDriven(): void
    {
        $policy = new ManageCrudFieldPolicy(
            titleFields: ['firstTitle', 'headline'],
            identityFields: ['sku'],
            longTextKeywords: ['memo'],
        );

        self::assertSame(['firstTitle', 'headline'], $policy->titleFields());
        self::assertSame(['sku'], $policy->identityFields());
        self::assertTrue($policy->isDiscoveryExcludedField('headline', []));
        self::assertTrue($policy->isDiscoveryExcludedField('publishedAt', ['publishedAt']));
        self::assertFalse($policy->isDiscoveryExcludedField('bodyText', []));
        self::assertTrue($policy->looksLikeLongTextField('privateMemo'));
    }

    public function testFieldTypeOverridesPreferRuntimeControllerPolicy(): void
    {
        $policy = new ManageCrudFieldPolicy(
            fieldTypeOverrides: [
                '*' => ['externalUrl' => 'url'],
                'App\\Cataloging\\Entity\\Catalog\\Product' => ['summary' => 'text'],
            ],
        );

        self::assertSame('url', $policy->explicitFieldType('App\\Other\\Entity\\Record', 'externalUrl'));
        self::assertSame('text', $policy->explicitFieldType('App\\Cataloging\\Entity\\Catalog\\Product', 'summary'));
        self::assertSame('textarea', $policy->explicitFieldType(
            'App\\Cataloging\\Entity\\Catalog\\Product',
            'summary',
            ['summary' => 'textarea'],
        ));
        self::assertNull($policy->explicitFieldType('App\\Other\\Entity\\Record', 'unknown', ['unknown' => 'invalid']));
    }

    public function testEmailAndUrlHeuristicsAreExplicitFallbackVocabulary(): void
    {
        $policy = new ManageCrudFieldPolicy(
            emailKeywords: ['mailbox'],
            urlKeywords: ['href'],
        );

        self::assertTrue($policy->looksLikeEmailField('supportMailbox'));
        self::assertFalse($policy->looksLikeEmailField('supportEmail'));
        self::assertTrue($policy->looksLikeUrlField('landingHref'));
        self::assertFalse($policy->looksLikeUrlField('landingUrl'));
    }
}
