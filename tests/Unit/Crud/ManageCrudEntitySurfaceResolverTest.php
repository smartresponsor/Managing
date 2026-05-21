<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Service\Crud\ManageCrudBehaviorPolicy;
use App\Managing\Service\Crud\ManageCrudEntitySurfaceResolver;
use PHPUnit\Framework\TestCase;

final class ManageCrudEntitySurfaceResolverTest extends TestCase
{
    public function testLabelsCanBeResolvedFromEntityShortNameOrExplicitOverrides(): void
    {
        $resolver = new ManageCrudEntitySurfaceResolver();

        self::assertSame(
            ['singular' => 'Surface Probe Entity', 'plural' => 'Surface Probe Entitys'],
            $resolver->labels(ManageCrudSurfaceProbeEntity::class),
        );

        self::assertSame(
            ['singular' => 'Custom Item', 'plural' => 'Custom Items'],
            $resolver->labels(ManageCrudSurfaceProbeEntity::class, 'Custom Item', 'Custom Items'),
        );
    }

    public function testSurfaceFieldsArePolicyDrivenAndFilteredByExistingEntityFields(): void
    {
        $resolver = new ManageCrudEntitySurfaceResolver(new ManageCrudBehaviorPolicy(
            searchFields: ['title', 'missingSearch'],
            statusFields: ['workflowStatus', 'missingStatus'],
            publicationFlagFields: ['visible', 'missingFlag'],
            publicationDateFields: ['publishedAt', 'missingDate'],
            auditDateFields: ['createdAt', 'updatedAt'],
            defaultSortFields: ['updatedAt', 'createdAt', 'id'],
        ));

        self::assertSame(['title'], $resolver->searchFields(ManageCrudSurfaceProbeEntity::class));
        self::assertSame(['workflowStatus'], $resolver->statusFields(ManageCrudSurfaceProbeEntity::class));
        self::assertSame(['visible'], $resolver->publicationFlagFields(ManageCrudSurfaceProbeEntity::class));
        self::assertSame(['createdAt', 'updatedAt', 'publishedAt'], $resolver->filterDateFields(ManageCrudSurfaceProbeEntity::class));
        self::assertSame(['updatedAt' => 'DESC'], $resolver->defaultSort(ManageCrudSurfaceProbeEntity::class));
    }

    public function testRuntimeCandidatesOverrideConfiguredCandidatesBeforeFiltering(): void
    {
        $resolver = new ManageCrudEntitySurfaceResolver(new ManageCrudBehaviorPolicy(
            searchFields: ['title'],
            statusFields: ['workflowStatus'],
            publicationFlagFields: ['visible'],
            publicationDateFields: ['publishedAt'],
        ));

        self::assertSame(['code'], $resolver->searchFields(ManageCrudSurfaceProbeEntity::class, ['code', 'missing']));
        self::assertSame(['state'], $resolver->statusFields(ManageCrudSurfaceProbeEntity::class, ['state', 'missing']));
        self::assertSame(['enabled'], $resolver->publicationFlagFields(ManageCrudSurfaceProbeEntity::class, ['enabled', 'missing']));
        self::assertSame(['createdAt', 'updatedAt', 'releasedAt'], $resolver->filterDateFields(ManageCrudSurfaceProbeEntity::class, ['releasedAt', 'missing']));
    }
}

final class ManageCrudSurfaceProbeEntity
{
    private int $id = 0;
    private string $title = '';
    private string $code = '';
    private string $workflowStatus = '';
    private string $state = '';
    private bool $visible = false;
    private bool $enabled = false;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;
    private ?\DateTimeImmutable $publishedAt = null;
    private ?\DateTimeImmutable $releasedAt = null;
}
