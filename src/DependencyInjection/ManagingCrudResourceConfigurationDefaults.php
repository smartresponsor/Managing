<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

/**
 * Default resource-scoring vocabulary for generated CRUD selection.
 */
final class ManagingCrudResourceConfigurationDefaults
{
    /** @return array<string, array<string, int>> */
    public static function primaryEntityBonusSuffixesByComponent(): array
    {
        return [
            'tagging' => ['\\TagAdminView' => 200],
            'messaging' => ['\\MessageAdminView' => 200],
        ];
    }

    /** @return array<string, array<string, int>> */
    public static function primaryEntityPenaltySuffixesByComponent(): array
    {
        return [
            'tagging' => [
                '\\TagOutboxEvent' => 50,
                '\\Tag' => 100,
                '\\TagPolicy' => 50,
            ],
            'messaging' => ['\\MessageEntity' => 100],
        ];
    }

    /** @return list<string> */
    public static function primaryTechnicalKeywords(): array
    {
        return [
            'attachment', 'audit', 'binding', 'change_request', 'control', 'delivery', 'destination',
            'event', 'history', 'idempotency', 'log', 'member', 'metric', 'outbox', 'pin', 'projection',
            'relation', 'review', 'snapshot', 'token', 'workflow', 'queue', 'batch', 'analytics',
            'credential', 'assignment', 'classification', 'reference', 'mapping', 'record', 'entity',
        ];
    }

    /** @return list<string> */
    public static function primaryBusinessKeywords(): array
    {
        return [
            'account', 'address', 'catalog', 'category', 'commission', 'content', 'currency', 'exchange',
            'message', 'order', 'page', 'payment', 'profile', 'product', 'record', 'shipment', 'tag',
            'tax', 'user', 'vendor',
        ];
    }

    /** @return list<string> */
    public static function generatedAttachmentMigrationComponents(): array
    {
        return ['attaching'];
    }
}
