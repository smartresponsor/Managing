<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Value\Crud\ManageCrudFieldUserProfileWriteRequest;
use App\Managing\Writer\Crud\ManageCrudConfigShapeFieldUserProfileWriter;
use App\Managing\Writer\Crud\ManageCrudNullFieldUserProfileWriter;
use PHPUnit\Framework\TestCase;

final class ManageCrudFieldUserProfileWriterTest extends TestCase
{
    public function testNullWriterRejectsWhenNoStorageBackendIsConfigured(): void
    {
        $result = (new ManageCrudNullFieldUserProfileWriter())->write(new ManageCrudFieldUserProfileWriteRequest(
            subjectIdentifier: 'user:42',
            pageName: 'index',
            hiddenFields: ['createdAt'],
        ));

        self::assertFalse($result->accepted);
        self::assertSame('field_user_profile_writer_not_configured', $result->reason);
    }

    public function testConfigShapeWriterBuildsSubjectDefaultPageRule(): void
    {
        $result = (new ManageCrudConfigShapeFieldUserProfileWriter())->write(new ManageCrudFieldUserProfileWriteRequest(
            subjectIdentifier: ' user:42 ',
            pageName: ' index ',
            visibleFields: ['status', 'status', ' '],
            hiddenFields: ['createdAt'],
        ));

        self::assertTrue($result->accepted);
        self::assertSame([
            'subjects' => [
                'user:42' => [
                    'defaults' => [
                        'index' => [
                            'visible' => ['status'],
                            'hidden' => ['createdAt'],
                        ],
                    ],
                    'resources' => [],
                ],
            ],
        ], $result->normalizedProfileConfig);
    }

    public function testConfigShapeWriterBuildsResourcePageRule(): void
    {
        $result = (new ManageCrudConfigShapeFieldUserProfileWriter())->write(new ManageCrudFieldUserProfileWriteRequest(
            subjectIdentifier: 'user:42',
            pageName: 'detail',
            hiddenFields: ['internalNote'],
            resourceClass: self::class,
        ));

        self::assertTrue($result->accepted);
        self::assertSame([
            'visible' => [],
            'hidden' => ['internalNote'],
        ], $result->normalizedProfileConfig['subjects']['user:42']['resources'][self::class]['detail']);
    }

    public function testConfigShapeWriterRejectsConflictingFieldPreference(): void
    {
        $result = (new ManageCrudConfigShapeFieldUserProfileWriter())->write(new ManageCrudFieldUserProfileWriteRequest(
            subjectIdentifier: 'user:42',
            pageName: 'index',
            visibleFields: ['status'],
            hiddenFields: ['status'],
        ));

        self::assertFalse($result->accepted);
        self::assertSame('field_user_profile_conflicting_field_preferences', $result->reason);
    }

    public function testConfigShapeWriterCanClearExistingPageRule(): void
    {
        $writer = new ManageCrudConfigShapeFieldUserProfileWriter([
            'subjects' => [
                'user:42' => [
                    'defaults' => [
                        'index' => ['hidden' => ['createdAt']],
                    ],
                ],
            ],
        ]);

        $result = $writer->write(new ManageCrudFieldUserProfileWriteRequest(
            subjectIdentifier: 'user:42',
            pageName: 'index',
        ));

        self::assertTrue($result->accepted);
        self::assertSame(['subjects' => []], $result->normalizedProfileConfig);
    }
}
