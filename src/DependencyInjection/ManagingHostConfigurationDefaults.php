<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

/**
 * Default host-discovery configuration values for the Managing bundle.
 */
final class ManagingHostConfigurationDefaults
{
    /** @return list<string> */
    public static function sourceRoots(): array
    {
        return ['src'];
    }

    /** @return list<string> */
    public static function namespacePrefixes(): array
    {
        return ['App\\'];
    }

    /** @return list<string> */
    public static function excludedNamespaces(): array
    {
        return ['App\\Managing\\'];
    }

    /** @return array<string, string> */
    public static function componentRootNames(): array
    {
        return [
            'attaching' => 'attachment',
            'billing' => 'billing',
            'cataloging' => 'catalog',
            'commissioning' => 'commission',
            'currencing' => 'currency',
            'exchanging' => 'exchange',
            'localizing' => 'localization',
            'messaging' => 'message',
            'ordering' => 'order',
            'paging' => 'page',
            'paying' => 'payment',
            'shipping' => 'shipment',
            'subscripting' => 'subscription',
            'tagging' => 'tag',
            'taxating' => 'tax',
            'vendoring' => 'vendor',
        ];
    }

    /** @return array<string, string> */
    public static function componentRootAliases(): array
    {
        return ['category' => 'cataloging'];
    }

    /** @return array<string, list<string>> */
    public static function includedEntitySuffixesByComponent(): array
    {
        return ['tagging' => ['\\TagAdminView']];
    }
}
