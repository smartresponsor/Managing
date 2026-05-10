<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection\Compiler;

use App\Managing\Service\Admin\ManageAdminRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ManageProviderPass implements CompilerPassInterface
{
    public const ADMIN_PROVIDER_TAG = 'manage.admin_provider';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(ManageAdminRegistry::class)) {
            return;
        }

        $registry = $container->getDefinition(ManageAdminRegistry::class);

        foreach (array_keys($container->findTaggedServiceIds(self::ADMIN_PROVIDER_TAG)) as $serviceId) {
            $registry->addMethodCall('addProvider', [new Reference($serviceId)]);
        }
    }
}
