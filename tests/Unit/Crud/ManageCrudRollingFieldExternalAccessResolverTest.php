<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Resolver\Crud\ManageCrudRollingFieldExternalAccessResolver;
use App\Managing\Value\Crud\ManageCrudFieldAccessContext;
use PHPUnit\Framework\TestCase;

final class ManageCrudRollingFieldExternalAccessResolverTest extends TestCase
{
    public function testMissingRollingServiceFailsClosedByDefault(): void
    {
        $resolver = new ManageCrudRollingFieldExternalAccessResolver();

        $decision = $resolver->decisionFor(new ManageCrudFieldAccessContext(
            componentKey: 'Managing',
            resourceClass: 'App\\Example\\Entity\\ExampleEntity',
            fieldName: 'internalCost',
            pageName: 'detail',
            subjectIdentifier: 'user:42',
        ));

        self::assertTrue($decision->denies());
        self::assertSame('rolling', $decision->source);
        self::assertSame('rolling_field_access_decision_service_not_configured', $decision->reason);
    }

    public function testMissingRollingServiceCanAbstainDuringStagedIntegration(): void
    {
        $resolver = new ManageCrudRollingFieldExternalAccessResolver(failureEffect: 'abstain');

        $decision = $resolver->decisionFor(new ManageCrudFieldAccessContext(
            componentKey: 'Managing',
            resourceClass: 'App\\Example\\Entity\\ExampleEntity',
            fieldName: 'status',
            pageName: 'index',
        ));

        self::assertTrue($decision->abstains());
        self::assertSame('rolling_field_access_decision_service_not_configured', $decision->reason);
    }
}
