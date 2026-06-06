<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Entity\Crud\ManageCrudFieldViewProfileRule;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileReadResult;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileWriteRequest;
use App\Managing\Writer\Crud\ManageCrudDoctrineFieldUserProfileWriter;
use App\Managing\WriterInterface\Crud\ManageCrudFieldUserProfileWriterInterface;
use PHPUnit\Framework\TestCase;

final class ManageCrudFieldUserProfileStorageShapeTest extends TestCase
{
    public function testEntityUsesDefaultResourceScopeForSubjectDefaultRule(): void
    {
        $rule = new ManageCrudFieldViewProfileRule(
            subjectIdentifier: ' user:42 ',
            pageName: ' index ',
            visibleFields: ['status', 'status', ' '],
            hiddenFields: ['createdAt'],
            actorIdentifier: ' admin ',
        );

        self::assertSame('user:42', $rule->subjectIdentifier());
        self::assertSame('index', $rule->pageName());
        self::assertSame('*', $rule->resourceKey());
        self::assertNull($rule->resourceClass());
        self::assertFalse($rule->targetsResource());
        self::assertSame(['status'], $rule->visibleFields());
        self::assertSame(['createdAt'], $rule->hiddenFields());
        self::assertSame('admin', $rule->actorIdentifier());
    }

    public function testEntityUsesResourceClassAsResourceScope(): void
    {
        $rule = new ManageCrudFieldViewProfileRule(
            subjectIdentifier: 'role:manager',
            pageName: 'detail',
            resourceClass: self::class,
            hiddenFields: ['internalNote'],
        );

        self::assertSame(self::class, $rule->resourceKey());
        self::assertSame(self::class, $rule->resourceClass());
        self::assertTrue($rule->targetsResource());
    }

    public function testReadResultNormalizesConfigShape(): void
    {
        $result = ManageCrudFieldUserProfileReadResult::available([
            'subjects' => [
                'user:42' => [
                    'defaults' => [
                        'index' => ['hidden' => ['createdAt', 'createdAt']],
                    ],
                ],
            ],
        ]);

        self::assertTrue($result->available);
        self::assertSame(['createdAt'], $result->normalizedProfileConfig['subjects']['user:42']['defaults']['index']['hidden']);
    }

    public function testDoctrineWriterDelegatesToRepositoryContract(): void
    {
        $repository = new class implements \App\Managing\RepositoryInterface\Crud\ManageCrudFieldViewProfileRuleRepositoryInterface {
            public ?ManageCrudFieldUserProfileWriteRequest $request = null;

            public function readProfileConfig(?string $subjectIdentifier = null): array
            {
                return ['subjects' => []];
            }

            public function replacePageRule(ManageCrudFieldUserProfileWriteRequest $request): array
            {
                $this->request = $request;

                return [
                    'subjects' => [
                        $request->normalizedSubjectIdentifier() => [
                            'defaults' => [
                                $request->normalizedPageName() => [
                                    'visible' => $request->normalizedVisibleFields(),
                                    'hidden' => $request->normalizedHiddenFields(),
                                ],
                            ],
                            'resources' => [],
                        ],
                    ],
                ];
            }
        };

        $writer = new ManageCrudDoctrineFieldUserProfileWriter($repository);
        self::assertInstanceOf(ManageCrudFieldUserProfileWriterInterface::class, $writer);

        $result = $writer->write(new ManageCrudFieldUserProfileWriteRequest(
            subjectIdentifier: 'user:42',
            pageName: 'index',
            hiddenFields: ['createdAt'],
        ));

        self::assertTrue($result->accepted);
        self::assertSame('field_user_profile_doctrine_write_applied', $result->reason);
        self::assertSame(['createdAt'], $result->normalizedProfileConfig['subjects']['user:42']['defaults']['index']['hidden']);
        self::assertInstanceOf(ManageCrudFieldUserProfileWriteRequest::class, $repository->request);
    }
}
