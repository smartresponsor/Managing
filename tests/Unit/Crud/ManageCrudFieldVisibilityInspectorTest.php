<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Factory\Crud\ManageCrudFieldDefinitionFactory;
use App\Managing\Inspector\Crud\ManageCrudFieldVisibilityInspector;
use App\Managing\Resolver\Crud\ManageCrudFieldVisibilityExplanationResolver;
use App\Managing\Value\Crud\ManageCrudFieldVisibilityInspectionRequest;
use PHPUnit\Framework\TestCase;

final class ManageCrudFieldVisibilityInspectorTest extends TestCase
{
    public function testInspectsRenderableField(): void
    {
        $report = $this->inspector()->inspect(new ManageCrudFieldVisibilityInspectionRequest(
            resourceClass: ManageVisibilityInspectionExampleEntity::class,
            fieldName: 'title',
            pageName: 'index',
        ));

        self::assertTrue($report->found());
        self::assertTrue($report->renderable());
        self::assertSame('visible', $report->statusLabel());
        self::assertSame('presentation', $report->decisionAxis());
        self::assertSame('title', $report->explanation?->definition->fieldName);
    }

    public function testExplainsFieldUnavailableOnRequestedPage(): void
    {
        $report = $this->inspector()->inspect(new ManageCrudFieldVisibilityInspectionRequest(
            resourceClass: ManageVisibilityInspectionExampleEntity::class,
            fieldName: 'description',
            pageName: 'index',
        ));

        self::assertTrue($report->found());
        self::assertSame('denied', $report->statusLabel());
        self::assertSame('availability', $report->decisionAxis());
        self::assertSame('field_not_available_on_page', $report->explanation?->finalDecision->reason);
    }

    public function testReportsUnknownField(): void
    {
        $report = $this->inspector()->inspect(new ManageCrudFieldVisibilityInspectionRequest(
            resourceClass: ManageVisibilityInspectionExampleEntity::class,
            fieldName: 'missingField',
            pageName: 'index',
        ));

        self::assertFalse($report->found());
        self::assertSame('not_found', $report->statusLabel());
        self::assertSame('not_found', $report->decisionAxis());
        self::assertSame('field_definition_not_found', $report->reason);
    }

    private function inspector(): ManageCrudFieldVisibilityInspector
    {
        return new ManageCrudFieldVisibilityInspector(
            new ManageCrudFieldDefinitionFactory(),
            new ManageCrudFieldVisibilityExplanationResolver(),
        );
    }
}

final class ManageVisibilityInspectionExampleEntity
{
    private int $id = 1;
    private string $title = 'Example';
    private string $description = 'Description';
}
