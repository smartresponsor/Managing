<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Handler\Crud\ManageCrudFieldUserProfileApplyHandler;
use App\Managing\Reader\Crud\ManageCrudConfigShapeFieldUserProfileReader;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileApplyRequest;
use App\Managing\Writer\Crud\ManageCrudConfigShapeFieldUserProfileWriter;
use PHPUnit\Framework\TestCase;

final class ManageCrudFieldUserProfileApplyHandlerTest extends TestCase
{
    public function testAppliesReviewedDefaultProfilePayloadThroughWriter(): void
    {
        $handler = new ManageCrudFieldUserProfileApplyHandler(new ManageCrudConfigShapeFieldUserProfileWriter());

        $result = $handler->apply(new ManageCrudFieldUserProfileApplyRequest(
            normalizedProfilePayload: [
                'subjects' => [
                    'user:42' => [
                        'defaults' => [
                            'index' => [
                                'hidden' => ['createdAt'],
                            ],
                        ],
                    ],
                ],
            ],
            reviewContext: [
                'surface' => 'managing_field_view_profile_review',
                'subject_key' => 'user:42',
                'profile_permission' => 'managing.field.profile.user_update',
                'mode' => 'replace',
                'page_name' => 'index',
            ],
            actorIdentifier: 'administering:operator',
        ));

        self::assertTrue($result->accepted);
        self::assertSame(['createdAt'], $result->normalizedProfileConfig['subjects']['user:42']['defaults']['index']['hidden']);
    }

    public function testRejectsUntrustedSurface(): void
    {
        $handler = new ManageCrudFieldUserProfileApplyHandler(new ManageCrudConfigShapeFieldUserProfileWriter());

        $result = $handler->apply(new ManageCrudFieldUserProfileApplyRequest(
            normalizedProfilePayload: ['subjects' => ['user:42' => ['defaults' => ['index' => ['hidden' => ['createdAt']]]]]],
            reviewContext: [
                'surface' => 'random_surface',
                'subject_key' => 'user:42',
                'profile_permission' => 'managing.field.profile.user_update',
                'page_name' => 'index',
            ],
        ));

        self::assertFalse($result->accepted);
        self::assertSame('field_user_profile_apply_untrusted_surface', $result->reason);
    }

    public function testRejectsMergeWithoutStorageReader(): void
    {
        $handler = new ManageCrudFieldUserProfileApplyHandler(new ManageCrudConfigShapeFieldUserProfileWriter());

        $result = $handler->apply(new ManageCrudFieldUserProfileApplyRequest(
            normalizedProfilePayload: ['subjects' => ['user:42' => ['defaults' => ['index' => ['visible' => ['status']]]]]],
            reviewContext: [
                'surface' => 'managing_field_view_profile_review',
                'subject_key' => 'user:42',
                'profile_permission' => 'managing.field.profile.user_update',
                'mode' => 'merge',
                'page_name' => 'index',
            ],
        ));

        self::assertFalse($result->accepted);
        self::assertSame('field_user_profile_merge_requires_storage_reader', $result->reason);
    }

    public function testAllowsMergeWhenStorageReaderIsConfigured(): void
    {
        $handler = new ManageCrudFieldUserProfileApplyHandler(
            new ManageCrudConfigShapeFieldUserProfileWriter([
                'subjects' => [
                    'user:42' => [
                        'defaults' => [
                            'index' => ['hidden' => ['createdAt']],
                        ],
                    ],
                ],
            ]),
            new ManageCrudConfigShapeFieldUserProfileReader([
                'subjects' => [
                    'user:42' => [
                        'defaults' => [
                            'index' => ['hidden' => ['createdAt']],
                        ],
                    ],
                ],
            ]),
        );

        $result = $handler->apply(new ManageCrudFieldUserProfileApplyRequest(
            normalizedProfilePayload: ['subjects' => ['user:42' => ['defaults' => ['detail' => ['visible' => ['status']]]]]],
            reviewContext: [
                'surface' => 'managing_field_view_profile_review',
                'subject_key' => 'user:42',
                'profile_permission' => 'managing.field.profile.user_update',
                'mode' => 'merge',
                'page_name' => 'detail',
            ],
        ));

        self::assertTrue($result->accepted);
        self::assertSame(['createdAt'], $result->normalizedProfileConfig['subjects']['user:42']['defaults']['index']['hidden']);
        self::assertSame(['status'], $result->normalizedProfileConfig['subjects']['user:42']['defaults']['detail']['visible']);
    }
}
