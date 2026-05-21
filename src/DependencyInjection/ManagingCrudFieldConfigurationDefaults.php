<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

/**
 * Default field-discovery vocabulary for EasyAdmin field selection.
 */
final class ManagingCrudFieldConfigurationDefaults
{
    /** @return list<string> */
    public static function titleCandidates(): array
    {
        return ['firstTitle', 'title', 'name', 'label'];
    }

    /** @return list<string> */
    public static function identityCandidates(): array
    {
        return ['code', 'slug', 'sku'];
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
