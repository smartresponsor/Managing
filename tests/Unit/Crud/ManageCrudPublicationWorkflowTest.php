<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Service\Crud\ManageCrudPublicationWorkflow;
use PHPUnit\Framework\TestCase;

final class ManageCrudPublicationWorkflowTest extends TestCase
{
    public function testPublicationSupportAndVisibilityPredicatesDelegateToStateHandler(): void
    {
        $workflow = new ManageCrudPublicationWorkflow();
        $entity = new ManageCrudPublicationWorkflowProbeEntity();

        self::assertTrue($workflow->supports(ManageCrudPublicationWorkflowProbeEntity::class, ['published'], []));
        self::assertTrue($workflow->canPublish(ManageCrudPublicationWorkflowProbeEntity::class, $entity, ['published'], []));
        self::assertFalse($workflow->canUnpublish(ManageCrudPublicationWorkflowProbeEntity::class, $entity, ['published'], []));
    }
}

final class ManageCrudPublicationWorkflowProbeEntity
{
    private bool $published = false;
}
