<?php

declare(strict_types=1);

namespace App\Managing\Value\Crud;

/**
 * Canonical permission keys used by Managing when field-level presentation needs an authorization decision.
 *
 * The keys are intentionally neutral and do not expose EasyAdmin classes. Rolling can evaluate them as
 * permissions, Administering can present them as administrative policy, and Managing can enforce the final
 * decision before EasyAdmin receives field definitions.
 */
final readonly class ManageCrudFieldPermissionVocabulary
{
    public const FIELD_VIEW = 'managing.field.view';
    public const FIELD_CONFIGURE = 'managing.field.configure';
    public const PROFILE_SELF_UPDATE = 'managing.field.profile.self_update';
    public const PROFILE_USER_UPDATE = 'managing.field.profile.user_update';
    public const PROFILE_ROLE_UPDATE = 'managing.field.profile.role_update';
    public const PROFILE_GROUP_UPDATE = 'managing.field.profile.group_update';
    public const PROFILE_ASSIGN = 'managing.field.profile.assign';

    /** @return list<string> */
    public static function all(): array
    {
        return [
            self::FIELD_VIEW,
            self::FIELD_CONFIGURE,
            self::PROFILE_SELF_UPDATE,
            self::PROFILE_USER_UPDATE,
            self::PROFILE_ROLE_UPDATE,
            self::PROFILE_GROUP_UPDATE,
            self::PROFILE_ASSIGN,
        ];
    }
}
