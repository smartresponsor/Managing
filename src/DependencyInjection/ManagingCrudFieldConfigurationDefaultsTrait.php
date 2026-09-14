<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

trait ManagingCrudFieldConfigurationDefaultsTrait
{
    /** @return list<string> */
    public static function crudFieldPrimaryIdentifierCandidates(): array
    {
        return ManagingCrudConfigurationDefaults::fieldPrimaryIdentifierCandidates();
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
    public static function crudFieldDescriptionCandidates(): array
    {
        return ManagingCrudConfigurationDefaults::fieldDescriptionCandidates();
    }

    /** @return list<string> */
    public static function crudFieldTechnicalExcludedFields(): array
    {
        return ManagingCrudConfigurationDefaults::fieldTechnicalExcludedFields();
    }

    /** @return array{defaults: array<string, array<string, list<string>>>, resources: array<string, array<string, array<string, list<string>>>>} */
    public static function crudFieldVisibility(): array
    {
        return ManagingCrudConfigurationDefaults::fieldVisibility();
    }

    /** @return array{subjects: array<string, array{defaults?: array<string, array<string, list<string>>>, resources?: array<string, array<string, array<string, list<string>>>>}>} */
    public static function crudFieldUserProfiles(): array
    {
        return ManagingCrudConfigurationDefaults::fieldUserProfiles();
    }

    public static function crudFieldUserProfileRuntimeBackend(): string
    {
        return ManagingCrudConfigurationDefaults::fieldUserProfileRuntimeBackend();
    }

    public static function crudFieldUserProfileReaderBackend(): string
    {
        return ManagingCrudConfigurationDefaults::fieldUserProfileReaderBackend();
    }

    public static function crudFieldUserProfileWriterBackend(): string
    {
        return ManagingCrudConfigurationDefaults::fieldUserProfileWriterBackend();
    }

    public static function crudFieldUserProfileEntityManagerService(): string
    {
        return ManagingCrudConfigurationDefaults::fieldUserProfileEntityManagerService();
    }

    public static function crudFieldExternalAccessBackend(): string
    {
        return ManagingCrudConfigurationDefaults::fieldExternalAccessBackend();
    }

    public static function crudFieldExternalAccessFailureEffect(): string
    {
        return ManagingCrudConfigurationDefaults::fieldExternalAccessFailureEffect();
    }

    public static function crudFieldExternalAccessRollingDecisionService(): string
    {
        return ManagingCrudConfigurationDefaults::fieldExternalAccessRollingDecisionService();
    }

    public static function crudFieldExternalAccessPermissionKey(): string
    {
        return ManagingCrudConfigurationDefaults::fieldExternalAccessPermissionKey();
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
