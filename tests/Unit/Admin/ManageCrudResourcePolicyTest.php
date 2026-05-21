<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Service\Admin\ManageCrudResourcePolicy;
use App\Managing\Value\ManageCrudResourceDefinition;
use PHPUnit\Framework\TestCase;

final class ManageCrudResourcePolicyTest extends TestCase
{
    public function testComponentSpecificInclusionRulesArePolicyDriven(): void
    {
        $policy = new ManageCrudResourcePolicy(
            includedEntitySuffixesByComponent: [
                'tagging' => ['\\TagAdminView'],
            ],
        );

        self::assertTrue($policy->shouldIncludeDiscoveredEntity('tagging', 'App\\Tagging\\Entity\\Core\\Tag\\TagAdminView'));
        self::assertFalse($policy->shouldIncludeDiscoveredEntity('tagging', 'App\\Tagging\\Entity\\Core\\Tag\\Tag'));
        self::assertTrue($policy->shouldIncludeDiscoveredEntity('cataloging', 'App\\Cataloging\\Entity\\Catalog\\Catalog'));
    }

    public function testPrimaryResourceScoringUsesPolicyData(): void
    {
        $policy = new ManageCrudResourcePolicy(
            componentRootNames: ['messaging' => 'message'],
            primaryEntityBonusSuffixesByComponent: [
                'messaging' => ['\\MessageAdminView' => 200],
            ],
            primaryEntityPenaltySuffixesByComponent: [
                'messaging' => ['\\MessageEntity' => 100],
            ],
            technicalKeywords: ['outbox'],
            businessKeywords: ['message'],
        );

        $adminView = new ManageCrudResourceDefinition(
            componentKey: 'messaging',
            resourceKey: 'message_admin_view',
            label: 'Message Admin View',
            entityClass: 'App\\Entity\\Message\\MessageAdminView',
        );
        $entity = new ManageCrudResourceDefinition(
            componentKey: 'messaging',
            resourceKey: 'message_entity',
            label: 'Message Entity',
            entityClass: 'App\\Entity\\Message\\MessageEntity',
        );

        self::assertGreaterThan(
            $policy->scoreResource($entity, 'MessageEntity', 'message'),
            $policy->scoreResource($adminView, 'MessageAdminView', 'messageadmin')
        );
        self::assertTrue(false === $policy->requiresAttachmentIdentifierMigration('attaching'));
    }

    public function testComponentKeyResolutionUsesRootPolicyAndAliases(): void
    {
        $policy = new ManageCrudResourcePolicy(
            componentRootNames: ['messaging' => 'message'],
            componentRootAliases: ['category' => 'cataloging'],
        );

        self::assertSame('messaging', $policy->componentKeyFromRootName('Message'));
        self::assertSame('cataloging', $policy->componentKeyFromRootName('Category'));
        self::assertNull($policy->componentKeyFromRootName('Unknown'));
    }

    public function testGeneratedControllerMigrationHookIsPolicyDriven(): void
    {
        $policy = new ManageCrudResourcePolicy(
            componentsRequiringAttachmentIdentifierMigration: ['attaching'],
        );

        self::assertTrue($policy->requiresAttachmentIdentifierMigration('attaching'));
        self::assertFalse($policy->requiresAttachmentIdentifierMigration('cataloging'));
    }
}
