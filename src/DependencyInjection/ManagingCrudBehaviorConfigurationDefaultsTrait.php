<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

trait ManagingCrudBehaviorConfigurationDefaultsTrait
{
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
}
