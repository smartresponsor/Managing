<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('managing');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('enabled_components')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
                ->arrayNode('disabled_resources')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
                ->arrayNode('menu_order')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
                ->arrayNode('menu_components')
                    ->scalarPrototype()->end()
                    ->defaultValue([
                        'analysing',
                        'applicating',
                        'attaching',
                        'billing',
                        'cataloging',
                        'commissioning',
                        'currencing',
                        'exchanging',
                        'localizing',
                        'messaging',
                        'ordering',
                        'paging',
                        'paying',
                        'rolling',
                        'shipping',
                        'subscripting',
                        'tagging',
                        'taxating',
                        'vendoring',
                    ])
                ->end()
                ->arrayNode('menu_excluded_components')
                    ->scalarPrototype()->end()
                    ->defaultValue([
                        'managing',
                        'cruding',
                        'interfacing',
                    ])
                ->end()
                ->booleanNode('host_scan_enabled')
                    ->defaultTrue()
                ->end()
                ->arrayNode('host_scan_source_roots')
                    ->scalarPrototype()->end()
                    ->defaultValue(['src'])
                ->end()
                ->arrayNode('host_scan_namespace_prefixes')
                    ->scalarPrototype()->end()
                    ->defaultValue(['App\\'])
                ->end()
                ->arrayNode('host_scan_excluded_namespaces')
                    ->scalarPrototype()->end()
                    ->defaultValue(['App\\Managing\\'])
                ->end()
                ->booleanNode('admin_enabled')
                    ->defaultTrue()
                ->end()
                ->scalarNode('admin_route_prefix')
                    ->defaultValue('/manage')
                ->end()
                ->arrayNode('admin_allowed_environments')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
                ->scalarNode('admin_required_role')
                    ->defaultValue('ROLE_ADMIN')
                ->end()
                ->booleanNode('admin_show_security_notes')
                    ->defaultTrue()
                ->end()
                ->scalarNode('admin_logout_path')
                    ->defaultValue('/logout')
                ->end()
                ->scalarNode('admin_logout_label')
                    ->defaultValue('Logout')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
