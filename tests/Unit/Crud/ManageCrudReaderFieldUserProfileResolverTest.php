<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Reader\Crud\ManageCrudConfigShapeFieldUserProfileReader;
use App\Managing\ReaderInterface\Crud\ManageCrudFieldUserProfileReaderInterface;
use App\Managing\Resolver\Crud\ManageCrudReaderFieldUserProfileResolver;
use App\Managing\Value\Crud\ManageCrudFieldDefinition;
use App\Managing\Value\Crud\ManageCrudFieldUserProfileReadResult;
use PHPUnit\Framework\TestCase;

final class ManageCrudReaderFieldUserProfileResolverTest extends TestCase
{
    public function testReaderBackedResolverReturnsSubjectProfileDecision(): void
    {
        $resolver = new ManageCrudReaderFieldUserProfileResolver(new ManageCrudConfigShapeFieldUserProfileReader([
            'subjects' => [
                'user:42' => [
                    'defaults' => [
                        'index' => ['hidden' => ['createdAt']],
                    ],
                ],
            ],
        ]));

        $decision = $resolver->decisionFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'createdAt', 'Created At'),
            'index',
            'user:42',
        );

        self::assertNotNull($decision);
        self::assertFalse($decision->visible);
        self::assertSame('user_profile_hidden', $decision->reason);
    }

    public function testReaderBackedResolverIgnoresUnavailableReader(): void
    {
        $resolver = new ManageCrudReaderFieldUserProfileResolver(new class implements ManageCrudFieldUserProfileReaderInterface {
            public function read(?string $subjectIdentifier = null): ManageCrudFieldUserProfileReadResult
            {
                return ManageCrudFieldUserProfileReadResult::unavailable('storage_off');
            }
        });

        self::assertNull($resolver->decisionFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'status', 'Status'),
            'index',
            'user:42',
        ));
    }

    public function testReaderBackedResolverCanUseWildcardSubjectFallback(): void
    {
        $resolver = new ManageCrudReaderFieldUserProfileResolver(new ManageCrudConfigShapeFieldUserProfileReader([
            'subjects' => [
                '*' => [
                    'defaults' => [
                        'detail' => ['visible' => ['description']],
                    ],
                ],
            ],
        ]));

        $decision = $resolver->decisionFor(
            new ManageCrudFieldDefinition('Managing', self::class, 'description', 'Description', defaultVisible: false),
            'detail',
            'user:missing',
        );

        self::assertNotNull($decision);
        self::assertTrue($decision->visible);
        self::assertSame('user_profile_visible', $decision->reason);
    }
}
