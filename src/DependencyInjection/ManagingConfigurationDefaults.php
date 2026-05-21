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

    /** @return array<string, array<string, int>> */
    public static function crudPrimaryEntityBonusSuffixesByComponent(): array
    {
        return ManagingCrudConfigurationDefaults::primaryEntityBonusSuffixesByComponent();
    }

    /** @return array<string, array<string, int>> */
    public static function crudPrimaryEntityPenaltySuffixesByComponent(): array
    {
        return ManagingCrudConfigurationDefaults::primaryEntityPenaltySuffixesByComponent();
    }

    /** @return list<string> */
    public static function crudPrimaryTechnicalKeywords(): array
    {
        return ManagingCrudConfigurationDefaults::primaryTechnicalKeywords();
    }

    /** @return list<string> */
    public static function crudPrimaryBusinessKeywords(): array
    {
        return ManagingCrudConfigurationDefaults::primaryBusinessKeywords();
    }

    /** @return list<string> */
    public static function crudGeneratedAttachmentMigrationComponents(): array
    {
        return ManagingCrudConfigurationDefaults::generatedAttachmentMigrationComponents();
    }

    /** @return list<string> */
    public static function crudBehaviorSearchFields(): array
    {
        return ManagingCrudConfigurationDefaults::behaviorSearchFields();
    }

    /** @return list<string> */
    public static function crudBehaviorStatusFields(): array
    {
        return ManagingCrudConfigurationDefaults::behaviorStatusFields();
    }

    /** @return list<string> */
    public static function crudBehaviorPublicationFlagFields(): array
    {
        return ManagingCrudConfigurationDefaults::behaviorPublicationFlagFields();
    }

    /** @return list<string> */
    public static function crudBehaviorPublicationDateFields(): array
    {
        return ManagingCrudConfigurationDefaults::behaviorPublicationDateFields();
    }

    /** @return list<string> */
    public static function crudBehaviorAuditDateFields(): array
    {
        return ManagingCrudConfigurationDefaults::behaviorAuditDateFields();
    }

    /** @return list<string> */
    public static function crudBehaviorDefaultSortFields(): array
    {
        return ManagingCrudConfigurationDefaults::behaviorDefaultSortFields();
    }

    /** @return list<string> */
    public static function crudFieldTitleCandidates(): array
    {
        return ManagingCrudConfigurationDefaults::fieldTitleCandidates();
    }

    /** @return list<string> */
    public static function crudFieldIdentityCandidates(): array
    {
        return ManagingCrudConfigurationDefaults::fieldIdentityCandidates();
    }

    /** @return list<string> */
    public static function crudFieldEmailKeywords(): array
    {
        return ManagingCrudConfigurationDefaults::fieldEmailKeywords();
    }

    /** @return list<string> */
    public static function crudFieldUrlKeywords(): array
    {
        return ManagingCrudConfigurationDefaults::fieldUrlKeywords();
    }

    /** @return list<string> */
    public static function crudFieldLongTextKeywords(): array
    {
        return ManagingCrudConfigurationDefaults::fieldLongTextKeywords();
    }
}
