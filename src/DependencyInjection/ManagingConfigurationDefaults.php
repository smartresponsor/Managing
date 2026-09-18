<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

/**
 * Backward-compatible facade over focused default configuration providers.
 *
 * The Symfony configuration tree keeps using this facade, while menu, host
 * discovery, and CRUD policy vocabularies live in focused classes.
 */
final class ManagingConfigurationDefaults
{
    use ManagingCrudBehaviorConfigurationDefaultsTrait;
    use ManagingCrudFieldConfigurationDefaultsTrait;

    /** @return list<string> */
    public static function leftMenu(): array
    {
        return ManagingMenuConfigurationDefaults::leftMenu();
    }

    /** @return list<string> */
    public static function menuExcludedComponents(): array
    {
        return ManagingMenuConfigurationDefaults::excludedComponents();
    }

    /** @return list<string> */
    public static function hostScanSourceRoots(): array
    {
        return ManagingHostConfigurationDefaults::sourceRoots();
    }

    /** @return list<string> */
    public static function hostScanNamespacePrefixes(): array
    {
        return ManagingHostConfigurationDefaults::namespacePrefixes();
    }

    /** @return list<string> */
    public static function hostScanExcludedNamespaces(): array
    {
        return ManagingHostConfigurationDefaults::excludedNamespaces();
    }

    /** @return array<string, string> */
    public static function componentRootNames(): array
    {
        return ManagingHostConfigurationDefaults::componentRootNames();
    }

    /** @return array<string, string> */
    public static function componentRootAliases(): array
    {
        return ManagingHostConfigurationDefaults::componentRootAliases();
    }

    /** @return array<string, list<string>> */
    public static function hostScanIncludedEntitySuffixesByComponent(): array
    {
        return ManagingHostConfigurationDefaults::includedEntitySuffixesByComponent();
    }
}
