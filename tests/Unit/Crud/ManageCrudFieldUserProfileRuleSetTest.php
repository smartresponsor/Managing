<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Value\Crud\ManageCrudFieldDefinition;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileRuleSet;
use PHPUnit\Framework\TestCase;

final class ManageCrudFieldUserProfileRuleSetTest extends TestCase
{
    public function testSubjectResourceProfileOverridesSubjectDefaultProfile(): void
    {
        $ruleSet = ManageCrudFieldUserProfileRuleSet::fromArray([
            'subjects' => [
                'user:42' => [
                    'defaults' => [
                        'index' => ['hidden' => ['status']],
                    ],
                    'resources' => [
                        self::class => [
                            'index' => ['visible' => ['status']],
                        ],
                    ],
                ],
            ],
        ]);

        $decision = $ruleSet->decisionFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'status', 'Status'),
            'index',
            'user:42',
        );

        self::assertNotNull($decision);
        self::assertTrue($decision->visible);
    }

    public function testWildcardSubjectActsAsFallbackProfile(): void
    {
        $ruleSet = ManageCrudFieldUserProfileRuleSet::fromArray([
            'subjects' => [
                '*' => [
                    'defaults' => [
                        'all' => ['hidden' => ['createdAt']],
                    ],
                ],
            ],
        ]);

        $decision = $ruleSet->decisionFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'createdAt', 'Created At'),
            'detail',
            'user:99',
        );

        self::assertNotNull($decision);
        self::assertFalse($decision->visible);
    }

    public function testAnonymousSubjectDoesNotReceivePersonalProfile(): void
    {
        $ruleSet = ManageCrudFieldUserProfileRuleSet::fromArray([
            'subjects' => [
                '*' => [
                    'defaults' => [
                        'all' => ['hidden' => ['createdAt']],
                    ],
                ],
            ],
        ]);

        self::assertNull($ruleSet->decisionFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'createdAt', 'Created At'),
            'detail',
            null,
        ));
    }
}
