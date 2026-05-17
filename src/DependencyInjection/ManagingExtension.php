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
        $container->setParameter('managing.menu_components', $config['menu_components']);
        $container->setParameter('managing.menu_excluded_components', $config['menu_excluded_components']);
        $container->setParameter('managing.content_enabled', $config['content_enabled']);
        $container->setParameter('managing.content_route_prefix', $config['content_route_prefix']);
        $container->setParameter('managing.content_allowed_environments', $config['content_allowed_environments']);
        $container->setParameter('managing.content_required_role', $config['content_required_role']);
        $container->setParameter('managing.content_logout_path', $config['content_logout_path']);
        $container->setParameter('managing.content_logout_label', $config['content_logout_label']);
        $container->setParameter('managing.host_scan_enabled', $config['host_scan_enabled']);
        $container->setParameter('managing.host_scan_source_roots', $config['host_scan_source_roots']);
        $container->setParameter('managing.host_scan_namespace_prefixes', $config['host_scan_namespace_prefixes']);
        $container->setParameter('managing.host_scan_excluded_namespaces', $config['host_scan_excluded_namespaces']);
        $container->setParameter('managing.business_index_resources', $config['business_index_resources']);

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
