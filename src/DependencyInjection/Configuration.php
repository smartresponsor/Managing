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
                ->variableNode('configured_components')
                    ->defaultValue([])
                ->end()
                ->variableNode('configured_resources')
                    ->defaultValue([])
                ->end()
                ->variableNode('configured_routes')
                    ->defaultValue([])
                ->end()
                ->variableNode('configured_forms')
                    ->defaultValue([])
                ->end()
                ->variableNode('configured_relations')
                    ->defaultValue([])
                ->end()
                ->variableNode('configured_probes')
                    ->defaultValue([])
                ->end()
            ->end();

        return $treeBuilder;
    }
}
