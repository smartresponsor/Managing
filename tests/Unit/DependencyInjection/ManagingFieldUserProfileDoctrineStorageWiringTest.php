<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\DependencyInjection;

use App\Managing\DependencyInjection\ManagingExtension;
use App\Managing\Repository\Crud\ManageDoctrineCrudFieldViewProfileRuleRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class ManagingFieldUserProfileDoctrineStorageWiringTest extends TestCase
{
    public function testDoctrineStorageBackendWiresConfiguredEntityManagerService(): void
    {
        $container = new ContainerBuilder();
        $repositoryId = ManageDoctrineCrudFieldViewProfileRuleRepository::class;
        $container->setDefinition($repositoryId, new Definition($repositoryId));

        $this->invokeDoctrineStorageConfigurator($container, [
            'crud_field_user_profile_reader_backend' => 'doctrine',
            'crud_field_user_profile_writer_backend' => 'none',
            'crud_field_user_profile_entity_manager_service' => 'doctrine.orm.system_entity_manager',
        ]);

        $argument = $container->getDefinition($repositoryId)->getArgument('$entityManager');

        self::assertInstanceOf(Reference::class, $argument);
        self::assertSame('doctrine.orm.system_entity_manager', (string) $argument);
    }

    public function testDoctrineStorageBackendIsNotForcedWhenReaderAndWriterAreDisabled(): void
    {
        $container = new ContainerBuilder();
        $repositoryId = ManageDoctrineCrudFieldViewProfileRuleRepository::class;
        $container->setDefinition($repositoryId, new Definition($repositoryId));

        $this->invokeDoctrineStorageConfigurator($container, [
            'crud_field_user_profile_reader_backend' => 'none',
            'crud_field_user_profile_writer_backend' => 'none',
            'crud_field_user_profile_entity_manager_service' => 'doctrine.orm.system_entity_manager',
        ]);

        self::assertFalse($container->getDefinition($repositoryId)->hasArgument('$entityManager'));
    }

    /** @param array<string, mixed> $config */
    private function invokeDoctrineStorageConfigurator(ContainerBuilder $container, array $config): void
    {
        $method = new \ReflectionMethod(ManagingExtension::class, 'configureFieldUserProfileDoctrineStorage');
        $method->setAccessible(true);
        $method->invoke(new ManagingExtension(), $container, $config);
    }
}
