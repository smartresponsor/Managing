<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Wires optional external field access backends without making Managing depend on Rolling at runtime.
 */
final class ManagingFieldExternalAccessBackendConfigurator
{
    private const INTERFACE_ID = 'App\\Managing\\ResolverInterface\\Crud\\ManageCrudFieldExternalAccessResolverInterface';
    private const NULL_RESOLVER_ID = 'App\\Managing\\Resolver\\Crud\\ManageCrudNullFieldExternalAccessResolver';
    private const ROLLING_RESOLVER_ID = 'App\\Managing\\Resolver\\Crud\\ManageCrudRollingFieldExternalAccessResolver';

    /** @param array<string, mixed> $config */
    public function configure(ContainerBuilder $container, array $config): void
    {
        $backend = trim((string) $config['crud_field_external_access_backend']);
        $failureEffect = trim((string) $config['crud_field_external_access_failure_effect']);

        $this->assertFailureEffect($failureEffect);
        $this->configureAlias($container, $backend);

        if ('rolling' === $backend) {
            $this->configureRollingResolver($container, $config, $failureEffect);
        }
    }

    private function assertFailureEffect(string $failureEffect): void
    {
        if (!in_array($failureEffect, ['deny', 'abstain'], true)) {
            throw new \InvalidArgumentException('Unsupported managing.crud_field_external_access_failure_effect "'.$failureEffect.'". Allowed values: deny, abstain.');
        }
    }

    private function configureAlias(ContainerBuilder $container, string $backend): void
    {
        $serviceId = [
            'none' => self::NULL_RESOLVER_ID,
            'rolling' => self::ROLLING_RESOLVER_ID,
        ][$backend] ?? null;

        if (null === $serviceId) {
            throw new \InvalidArgumentException('Unsupported managing.crud_field_external_access_backend "'.$backend.'". Allowed values: none, rolling.');
        }

        $container->setAlias(self::INTERFACE_ID, $serviceId);
    }

    /** @param array<string, mixed> $config */
    private function configureRollingResolver(ContainerBuilder $container, array $config, string $failureEffect): void
    {
        if (!$container->hasDefinition(self::ROLLING_RESOLVER_ID)) {
            return;
        }

        $serviceId = trim((string) $config['crud_field_external_access_rolling_decision_service']);
        if ('' === $serviceId) {
            throw new \InvalidArgumentException('managing.crud_field_external_access_rolling_decision_service must not be empty when rolling field access is enabled.');
        }

        $container->getDefinition(self::ROLLING_RESOLVER_ID)
            ->setArgument('$permissionKey', (string) $config['crud_field_external_access_permission_key'])
            ->setArgument('$failureEffect', $failureEffect)
            ->setArgument('$rollingFieldAccessDecisionService', new Reference($serviceId, ContainerInterface::NULL_ON_INVALID_REFERENCE));
    }
}
