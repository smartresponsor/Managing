<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Service\Crud\ManageCrudBehaviorPolicy;
use PHPUnit\Framework\TestCase;

final class ManageCrudBehaviorPolicyTest extends TestCase
{
    public function testCrudBehaviorDefaultsArePolicyDriven(): void
    {
        $policy = new ManageCrudBehaviorPolicy(
            searchFields: ['headline', 'sku'],
            statusFields: ['workflowState'],
            publicationFlagFields: ['visible'],
            publicationDateFields: ['visibleAt'],
            auditDateFields: ['createdOn'],
            defaultSortFields: ['visibleAt', 'id'],
        );

        self::assertSame(['headline', 'sku'], $policy->searchFields());
        self::assertSame(['workflowState'], $policy->statusFields());
        self::assertSame(['visible'], $policy->publicationFlagFields());
        self::assertSame(['visibleAt'], $policy->publicationDateFields());
        self::assertSame(['createdOn', 'visibleAt'], $policy->filterDateFields());
        self::assertSame(['visibleAt', 'id'], $policy->defaultSortFields());
    }

    public function testRuntimeControllerCandidatesOverrideGenericPolicy(): void
    {
        $policy = new ManageCrudBehaviorPolicy(
            searchFields: ['title'],
            statusFields: ['status'],
            publicationFlagFields: ['published'],
            publicationDateFields: ['publishedAt'],
        );

        self::assertSame(['domainTitle'], $policy->searchFields(['domainTitle']));
        self::assertSame(['workflowStatus'], $policy->statusFields(['workflowStatus']));
        self::assertSame(['isLive'], $policy->publicationFlagFields(['isLive']));
        self::assertSame(['wentLiveAt'], $policy->publicationDateFields(['wentLiveAt']));
        self::assertSame(['createdAt', 'updatedAt', 'wentLiveAt'], $policy->filterDateFields(['wentLiveAt']));
    }

    public function testEmptyRuntimeCandidatesKeepConfiguredDefaults(): void
    {
        $policy = new ManageCrudBehaviorPolicy(searchFields: ['title']);

        self::assertSame(['title'], $policy->searchFields([]));
        self::assertSame(['title'], $policy->searchFields(['', 'title', 'title']));
    }
}
