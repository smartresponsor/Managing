<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

/**
 * Default field-discovery vocabulary for EasyAdmin field selection.
 */
final class ManagingCrudFieldConfigurationDefaults
{
    /** @return list<string> */
    public static function primaryIdentifierCandidates(): array
    {
        return ['id'];
    }

    /** @return list<string> */
    public static function titleCandidates(): array
    {
        return ['firstTitle', 'title', 'nameEntity', 'label'];
    }

    /** @return list<string> */
    public static function identityCandidates(): array
    {
        return ['code', 'slug', 'sku'];
    }

    /** @return list<string> */
    public static function descriptionCandidates(): array
    {
        return ['description'];
    }

    /** @return list<string> */
    public static function technicalExcludedFields(): array
    {
        return [];
    }

    /** @return array{defaults: array<string, array<string, list<string>>>, resources: array<string, array<string, array<string, list<string>>>>} */
    public static function visibility(): array
    {
        return [
            'defaults' => [],
            'resources' => [],
        ];
    }

    /** @return array{subjects: array<string, array{defaults?: array<string, array<string, list<string>>>, resources?: array<string, array<string, array<string, list<string>>>>}>} */
    public static function userProfiles(): array
    {
        return [
            'subjects' => [],
        ];
    }

    public static function userProfileRuntimeBackend(): string
    {
        return 'config';
    }

    public static function userProfileReaderBackend(): string
    {
        return 'none';
    }

    public static function userProfileWriterBackend(): string
    {
        return 'none';
    }

    public static function userProfileEntityManagerService(): string
    {
        return 'doctrine.orm.system_entity_manager';
    }

    public static function externalAccessBackend(): string
    {
        return 'none';
    }

    public static function externalAccessFailureEffect(): string
    {
        return 'deny';
    }

    public static function externalAccessRollingDecisionService(): string
    {
        return 'App\\Rolling\\ServiceInterface\\Administration\\RollingFieldAccessDecisionServiceInterface';
    }

    public static function externalAccessPermissionKey(): string
    {
        return 'managing.field.view';
    }

    /** @return list<string> */
    public static function emailKeywords(): array
    {
        return ['email'];
    }

    /** @return list<string> */
    public static function urlKeywords(): array
    {
        return ['url', 'link'];
    }

    /** @return list<string> */
    public static function longTextKeywords(): array
    {
        return ['description', 'summary', 'message', 'content', 'body', 'note', 'payload', 'details'];
    }
}
