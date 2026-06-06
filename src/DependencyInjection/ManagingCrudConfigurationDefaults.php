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
    public static function fieldPrimaryIdentifierCandidates(): array
    {
        return ManagingCrudFieldConfigurationDefaults::primaryIdentifierCandidates();
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
    public static function fieldDescriptionCandidates(): array
    {
        return ManagingCrudFieldConfigurationDefaults::descriptionCandidates();
    }

    /** @return list<string> */
    public static function fieldTechnicalExcludedFields(): array
    {
        return ManagingCrudFieldConfigurationDefaults::technicalExcludedFields();
    }

    /** @return array{defaults: array<string, array<string, list<string>>>, resources: array<string, array<string, array<string, list<string>>>>} */
    public static function fieldVisibility(): array
    {
        return ManagingCrudFieldConfigurationDefaults::visibility();
    }

    /** @return array{subjects: array<string, array{defaults?: array<string, array<string, list<string>>>, resources?: array<string, array<string, array<string, list<string>>>>}>} */
    public static function fieldUserProfiles(): array
    {
        return ManagingCrudFieldConfigurationDefaults::userProfiles();
    }

    public static function fieldUserProfileRuntimeBackend(): string
    {
        return ManagingCrudFieldConfigurationDefaults::userProfileRuntimeBackend();
    }

    public static function fieldUserProfileReaderBackend(): string
    {
        return ManagingCrudFieldConfigurationDefaults::userProfileReaderBackend();
    }

    public static function fieldUserProfileWriterBackend(): string
    {
        return ManagingCrudFieldConfigurationDefaults::userProfileWriterBackend();
    }

    public static function fieldUserProfileEntityManagerService(): string
    {
        return ManagingCrudFieldConfigurationDefaults::userProfileEntityManagerService();
    }

    public static function fieldExternalAccessBackend(): string
    {
        return ManagingCrudFieldConfigurationDefaults::externalAccessBackend();
    }

    public static function fieldExternalAccessFailureEffect(): string
    {
        return ManagingCrudFieldConfigurationDefaults::externalAccessFailureEffect();
    }

    public static function fieldExternalAccessRollingDecisionService(): string
    {
        return ManagingCrudFieldConfigurationDefaults::externalAccessRollingDecisionService();
    }

    public static function fieldExternalAccessPermissionKey(): string
    {
        return ManagingCrudFieldConfigurationDefaults::externalAccessPermissionKey();
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
