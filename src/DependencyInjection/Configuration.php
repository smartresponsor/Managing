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
                        'accessing',
                        'analysing',
                        'applicating',
                        'attaching',
                        'billing',
                        'bridging',
                        'cataloging',
                        'commissioning',
                        'cruding',
                        'currencing',
                        'exchanging',
                        'indexing',
                        'interfacing',
                        'localizing',
                        'managing',
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
                    ])
                ->end()
                ->booleanNode('content_enabled')
                    ->defaultTrue()
                ->end()
                ->scalarNode('content_route_prefix')
                    ->defaultValue('/manage')
                ->end()
                ->arrayNode('content_allowed_environments')
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
                ->scalarNode('content_required_role')
                    ->defaultValue('ROLE_ADMIN')
                ->end()
                ->scalarNode('content_logout_path')
                    ->defaultValue('/logout')
                ->end()
                ->scalarNode('content_logout_label')
                    ->defaultValue('Logout')
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
                ->variableNode('business_index_resources')
                    ->defaultValue([])
                ->end()
            ->end();

        return $treeBuilder;
    }
}
