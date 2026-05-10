<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class ManagingExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array<int, array<string, mixed>> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('managing.enabled_components', $config['enabled_components']);
        $container->setParameter('managing.disabled_resources', $config['disabled_resources']);
        $container->setParameter('managing.menu_order', $config['menu_order']);
        $container->setParameter('managing.admin_enabled', $config['admin_enabled']);
        $container->setParameter('managing.admin_route_prefix', $config['admin_route_prefix']);
        $container->setParameter('managing.admin_allowed_environments', $config['admin_allowed_environments']);
        $container->setParameter('managing.admin_required_role', $config['admin_required_role']);
        $container->setParameter('managing.admin_show_security_notes', $config['admin_show_security_notes']);
        $container->setParameter('managing.configured_components', $config['configured_components']);
        $container->setParameter('managing.configured_resources', $config['configured_resources']);
        $container->setParameter('managing.configured_routes', $config['configured_routes']);
        $container->setParameter('managing.configured_forms', $config['configured_forms']);
        $container->setParameter('managing.configured_relations', $config['configured_relations']);
        $container->setParameter('managing.configured_probes', $config['configured_probes']);

        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__, 2).'/config'));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('twig', [
            'paths' => [
                dirname(__DIR__, 2).'/templates',
            ],
        ]);
    }
}
