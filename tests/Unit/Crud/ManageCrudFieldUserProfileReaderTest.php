<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Reader\Crud\ManageCrudConfigShapeFieldUserProfileReader;
use App\Managing\Reader\Crud\ManageCrudDoctrineFieldUserProfileReader;
use App\Managing\Reader\Crud\ManageCrudNullFieldUserProfileReader;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileWriteRequest;
use PHPUnit\Framework\TestCase;

final class ManageCrudFieldUserProfileReaderTest extends TestCase
{
    public function testNullReaderReportsUnavailable(): void
    {
        $result = (new ManageCrudNullFieldUserProfileReader())->read('user:42');

        self::assertFalse($result->available);
        self::assertSame('field_user_profile_reader_not_configured', $result->reason);
    }

    public function testConfigShapeReaderCanReadOneSubject(): void
    {
        $reader = new ManageCrudConfigShapeFieldUserProfileReader([
            'subjects' => [
                'user:42' => [
                    'defaults' => [
                        'index' => ['hidden' => ['createdAt']],
                    ],
                ],
                'user:99' => [
                    'defaults' => [
                        'detail' => ['visible' => ['status']],
                    ],
                ],
            ],
        ]);

        $result = $reader->read('user:42');

        self::assertTrue($result->available);
        self::assertArrayHasKey('user:42', $result->normalizedProfileConfig['subjects']);
        self::assertArrayNotHasKey('user:99', $result->normalizedProfileConfig['subjects']);
    }

    public function testDoctrineReaderDelegatesToRepositoryContract(): void
    {
        $repository = new class implements \App\Managing\RepositoryInterface\Crud\ManageCrudFieldViewProfileRuleRepositoryInterface {
            public ?string $subjectIdentifier = null;

            public function readProfileConfig(?string $subjectIdentifier = null): array
            {
                $this->subjectIdentifier = $subjectIdentifier;

                return ['subjects' => ['user:42' => ['defaults' => ['index' => ['hidden' => ['createdAt']]], 'resources' => []]]];
            }

            public function replacePageRule(ManageCrudFieldUserProfileWriteRequest $request): array
            {
                return ['subjects' => []];
            }
        };

        $result = (new ManageCrudDoctrineFieldUserProfileReader($repository))->read('user:42');

        self::assertTrue($result->available);
        self::assertSame('field_user_profile_doctrine_read_available', $result->reason);
        self::assertSame('user:42', $repository->subjectIdentifier);
    }
}
