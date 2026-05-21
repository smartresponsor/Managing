<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Service\Admin\ManageCrudResourceScorer;
use App\Managing\Value\ManageCrudResourceDefinition;
use PHPUnit\Framework\TestCase;

final class ManageCrudResourceScorerTest extends TestCase
{
    public function testScoresBusinessFacingResourceAboveTechnicalEntity(): void
    {
        $scorer = new ManageCrudResourceScorer(
            primaryEntityBonusSuffixesByComponent: ['messaging' => ['\\MessageAdminView' => 200]],
            primaryEntityPenaltySuffixesByComponent: ['messaging' => ['\\MessageEntity' => 100]],
            technicalKeywords: ['outbox'],
            businessKeywords: ['message'],
        );

        $businessResource = new ManageCrudResourceDefinition(
            componentKey: 'messaging',
            resourceKey: 'message_admin_view',
            label: 'Message Admin View',
            entityClass: 'App\\Messaging\\Entity\\Message\\MessageAdminView',
        );
        $technicalResource = new ManageCrudResourceDefinition(
            componentKey: 'messaging',
            resourceKey: 'message_outbox_entity',
            label: 'Message Outbox Entity',
            entityClass: 'App\\Messaging\\Entity\\Message\\MessageEntity',
        );

        self::assertGreaterThan(
            $scorer->score($technicalResource, 'MessageEntity', 'messageoutboxentity', 'message'),
            $scorer->score($businessResource, 'MessageAdminView', 'messageadminview', 'message'),
        );
    }
}
