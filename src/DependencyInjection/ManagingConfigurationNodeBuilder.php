<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;

/**
 * Small Symfony Config Definition helper for Managing's repetitive nodes.
 *
 * The public configuration shape stays owned by Configuration; this helper only
 * centralizes the low-level TreeBuilder grammar for scalar lists and keyed maps.
 */
final class ManagingConfigurationNodeBuilder
{
    /** @param list<string> $default */
    public static function scalarList(NodeBuilder $children, string $nameEntity, array $default = []): void
    {
        $children
            ->arrayNode($nameEntity)
                ->scalarPrototype()->end()
                ->defaultValue($default)
            ->end();
    }

    /** @param array<string, string> $default */
    public static function scalarMap(NodeBuilder $children, string $nameEntity, string $keyName, array $default = []): void
    {
        $children
            ->arrayNode($nameEntity)
                ->useAttributeAsKey($keyName)
                ->scalarPrototype()->end()
                ->defaultValue($default)
            ->end();
    }

    /** @param array<string, list<string>> $default */
    public static function scalarListMap(NodeBuilder $children, string $nameEntity, string $keyName, array $default = []): void
    {
        $children
            ->arrayNode($nameEntity)
                ->useAttributeAsKey($keyName)
                ->arrayPrototype()
                    ->scalarPrototype()->end()
                ->end()
                ->defaultValue($default)
            ->end();
    }

    /** @param array<string, array<string, int>> $default */
    public static function intMapMap(NodeBuilder $children, string $nameEntity, string $outerKeyName, string $innerKeyName, array $default = []): void
    {
        $children
            ->arrayNode($nameEntity)
                ->useAttributeAsKey($outerKeyName)
                ->arrayPrototype()
                    ->useAttributeAsKey($innerKeyName)
                    ->integerPrototype()->end()
                ->end()
                ->defaultValue($default)
            ->end();
    }

    /** @param array<string, array<string, string>> $default */
    public static function scalarMapMap(NodeBuilder $children, string $nameEntity, string $outerKeyName, string $innerKeyName, array $default = []): void
    {
        $children
            ->arrayNode($nameEntity)
                ->useAttributeAsKey($outerKeyName)
                ->arrayPrototype()
                    ->useAttributeAsKey($innerKeyName)
                    ->scalarPrototype()->end()
                ->end()
                ->defaultValue($default)
            ->end();
    }

    public static function boolean(NodeBuilder $children, string $nameEntity, bool $default): void
    {
        $node = $children->booleanNode($nameEntity);

        if ($default) {
            $node->defaultTrue()->end();

            return;
        }

        $node->defaultFalse()->end();
    }

    public static function scalar(NodeBuilder $children, string $nameEntity, string $default): void
    {
        $children
            ->scalarNode($nameEntity)
                ->defaultValue($default)
            ->end();
    }
}
