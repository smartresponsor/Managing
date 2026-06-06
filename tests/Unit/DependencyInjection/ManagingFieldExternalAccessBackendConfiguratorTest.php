<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\DependencyInjection;

use App\Managing\DependencyInjection\ManagingFieldExternalAccessBackendConfigurator;
use App\Managing\Resolver\Crud\ManageCrudRollingFieldExternalAccessResolver;
use App\Managing\ResolverInterface\Crud\ManageCrudFieldExternalAccessResolverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ManagingFieldExternalAccessBackendConfiguratorTest extends TestCase
{
    public function testRollingBackendAliasesExternalResolverAndWiresOptionalDecisionService(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition(
            ManageCrudRollingFieldExternalAccessResolver::class,
            new Definition(ManageCrudRollingFieldExternalAccessResolver::class),
        );

        (new ManagingFieldExternalAccessBackendConfigurator())->configure($container, [
            'crud_field_external_access_backend' => 'rolling',
            'crud_field_external_access_failure_effect' => 'deny',
            'crud_field_external_access_rolling_decision_service' => 'rolling.field_decider',
            'crud_field_external_access_permission_key' => 'managing.field.view',
        ]);

        self::assertSame(
            ManageCrudRollingFieldExternalAccessResolver::class,
            (string) $container->getAlias(ManageCrudFieldExternalAccessResolverInterface::class),
        );

        $reference = $container
            ->getDefinition(ManageCrudRollingFieldExternalAccessResolver::class)
            ->getArgument('$rollingFieldAccessDecisionService');

        self::assertInstanceOf(Reference::class, $reference);
        self::assertSame('rolling.field_decider', (string) $reference);
    }
}
