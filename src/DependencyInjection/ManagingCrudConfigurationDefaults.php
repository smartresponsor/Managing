<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

/**
 * Backward-compatible facade over focused CRUD default providers.
 */
final class ManagingCrudConfigurationDefaults
{
    /** @return array<string, array<string, int>> */
    public static function primaryEntityBonusSuffixesByComponent(): array
    {
        return ManagingCrudResourceConfigurationDefaults::primaryEntityBonusSuffixesByComponent();
    }

    /** @return array<string, array<string, int>> */
    public static function primaryEntityPenaltySuffixesByComponent(): array
    {
        return ManagingCrudResourceConfigurationDefaults::primaryEntityPenaltySuffixesByComponent();
    }

    /** @return list<string> */
    public static function primaryTechnicalKeywords(): array
    {
        return ManagingCrudResourceConfigurationDefaults::primaryTechnicalKeywords();
    }

    /** @return list<string> */
    public static function primaryBusinessKeywords(): array
    {
        return ManagingCrudResourceConfigurationDefaults::primaryBusinessKeywords();
    }

    /** @return list<string> */
    public static function generatedAttachmentMigrationComponents(): array
    {
        return ManagingCrudResourceConfigurationDefaults::generatedAttachmentMigrationComponents();
    }

    /** @return list<string> */
    public static function behaviorSearchFields(): array
    {
        return ManagingCrudBehaviorConfigurationDefaults::searchFields();
    }

    /** @return list<string> */
    public static function behaviorStatusFields(): array
    {
        return ManagingCrudBehaviorConfigurationDefaults::statusFields();
    }

    /** @return list<string> */
    public static function behaviorPublicationFlagFields(): array
    {
        return ManagingCrudBehaviorConfigurationDefaults::publicationFlagFields();
    }

    /** @return list<string> */
    public static function behaviorPublicationDateFields(): array
    {
        return ManagingCrudBehaviorConfigurationDefaults::publicationDateFields();
    }

    /** @return list<string> */
    public static function behaviorAuditDateFields(): array
    {
        return ManagingCrudBehaviorConfigurationDefaults::auditDateFields();
    }

    /** @return list<string> */
    public static function behaviorDefaultSortFields(): array
    {
        return ManagingCrudBehaviorConfigurationDefaults::defaultSortFields();
    }

    /** @return list<string> */
    public static function fieldTitleCandidates(): array
    {
        return ManagingCrudFieldConfigurationDefaults::titleCandidates();
    }

    /** @return list<string> */
    public static function fieldIdentityCandidates(): array
    {
        return ManagingCrudFieldConfigurationDefaults::identityCandidates();
    }

    /** @return list<string> */
    public static function fieldEmailKeywords(): array
    {
        return ManagingCrudFieldConfigurationDefaults::emailKeywords();
    }

    /** @return list<string> */
    public static function fieldUrlKeywords(): array
    {
        return ManagingCrudFieldConfigurationDefaults::urlKeywords();
    }

    /** @return list<string> */
    public static function fieldLongTextKeywords(): array
    {
        return ManagingCrudFieldConfigurationDefaults::longTextKeywords();
    }
}
