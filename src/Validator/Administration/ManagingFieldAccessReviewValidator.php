<?php

declare(strict_types=1);

namespace App\Managing\Validator\Administration;

use App\Managing\Value\Administration\ManagingFieldPermissionVocabulary;

/**
 * Validates that reviewed ACL metadata belongs to the Managing field-access surface.
 *
 * Malformed review context is rejected fail-closed. Missing optional context may still
 * be accepted when the permission key itself is one of Managing's canonical policy keys.
 */
final class ManagingFieldAccessReviewValidator
{
    /**
     * @param array<string, mixed> $safeReviewPayload
     */
    public function isManagingFieldAccessReview(
        string $permissionOrRoleKey,
        string $scopeKey,
        string $mutationType,
        array $safeReviewPayload,
    ): bool {
        if (!str_starts_with($permissionOrRoleKey, 'managing.field.')) {
            return false;
        }

        if (!str_starts_with($scopeKey, 'component:managing')) {
            return false;
        }

        if (!in_array($mutationType, ['permission.grant', 'permission.revoke', 'acl.allow', 'acl.deny'], true)) {
            return false;
        }

        $safeContext = $safeReviewPayload['safe_context'] ?? null;

        if (null !== $safeContext && !is_array($safeContext)) {
            return false;
        }

        if (is_array($safeContext)) {
            $target = $safeContext['target'] ?? null;

            if (null !== $target && !is_array($target)) {
                return false;
            }

            if (is_array($target) && array_key_exists('component', $target)) {
                $component = $target['component'];

                return is_string($component) && 'managing' === strtolower(trim($component));
            }

            $surface = $safeContext['surface'] ?? null;

            if (null !== $surface && !is_string($surface)) {
                return false;
            }

            if ('managing_field_access_mutation_review' === $surface) {
                return true;
            }
        }

        return in_array($permissionOrRoleKey, ManagingFieldPermissionVocabulary::policyKeys(), true);
    }
}
