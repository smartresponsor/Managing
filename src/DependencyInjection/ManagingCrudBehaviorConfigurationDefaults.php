<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

/**
 * Default CRUD surface behavior vocabulary.
 */
final class ManagingCrudBehaviorConfigurationDefaults
{
    /** @return list<string> */
    public static function searchFields(): array
    {
        return ['firstTitle', 'title', 'name', 'label', 'code', 'slug', 'sku', 'status', 'state'];
    }

    /** @return list<string> */
    public static function statusFields(): array
    {
        return ['status', 'state'];
    }

    /** @return list<string> */
    public static function publicationFlagFields(): array
    {
        return ['published', 'isPublished', 'enabled', 'active'];
    }

    /** @return list<string> */
    public static function publicationDateFields(): array
    {
        return ['publishedAt', 'publishAt'];
    }

    /** @return list<string> */
    public static function auditDateFields(): array
    {
        return ['createdAt', 'updatedAt'];
    }

    /** @return list<string> */
    public static function defaultSortFields(): array
    {
        return ['updatedAt', 'createdAt', 'publishedAt', 'id'];
    }
}
