<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\DependencyInjection;

use App\Managing\DependencyInjection\ManagingConfigurationDefaults;
use App\Managing\DependencyInjection\ManagingParameterLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ManagingParameterLoaderTest extends TestCase
{
    public function testParameterMapKeepsCriticalCrudAndAdminKeys(): void
    {
        $map = ManagingParameterLoader::parameterMap();

        self::assertSame('managing.left_menu', $map['left_menu']);
        self::assertSame('managing.host_scan_enabled', $map['host_scan_enabled']);
        self::assertSame('managing.crud_field_type_overrides', $map['crud_field_type_overrides']);
        self::assertSame('managing.admin_logout_label', $map['admin_logout_label']);
    }

    public function testLoadPublishesProcessedConfigAsContainerParameters(): void
    {
        $container = new ContainerBuilder();
        $config = $this->minimalProcessedConfig();

        (new ManagingParameterLoader())->load($container, $config, '/tmp/managing');

        self::assertSame('/tmp/managing', $container->getParameter('managing.bundle_dir'));
        self::assertSame(['cataloging'], $container->getParameter('managing.left_menu'));
        self::assertSame(['Category' => 'cataloging'], $container->getParameter('managing.component_root_aliases'));
        self::assertSame(['email'], $container->getParameter('managing.crud_field_email_keywords'));
        self::assertSame('Sign out', $container->getParameter('managing.admin_logout_label'));
    }

    /**
     * @return array<string, mixed>
     */
    private function minimalProcessedConfig(): array
    {
        return [
            'enabled_components' => [],
            'disabled_resources' => [],
            'menu_order' => [],
            'left_menu' => ['cataloging'],
            'menu_excluded_components' => ManagingConfigurationDefaults::menuExcludedComponents(),
            'host_scan_enabled' => true,
            'host_scan_source_roots' => ['src'],
            'host_scan_namespace_prefixes' => ['App\\'],
            'host_scan_excluded_namespaces' => [],
            'component_root_names' => ['cataloging' => 'catalog'],
            'component_root_aliases' => ['Category' => 'cataloging'],
            'host_scan_included_entity_suffixes_by_component' => [],
            'crud_primary_entity_bonus_suffixes_by_component' => [],
            'crud_primary_entity_penalty_suffixes_by_component' => [],
            'crud_primary_technical_keywords' => [],
            'crud_primary_business_keywords' => [],
            'crud_generated_attachment_migration_components' => [],
            'crud_behavior_search_fields' => ['name'],
            'crud_behavior_status_fields' => ['status'],
            'crud_behavior_publication_flag_fields' => ['published'],
            'crud_behavior_publication_date_fields' => ['publishedAt'],
            'crud_behavior_audit_date_fields' => ['createdAt'],
            'crud_behavior_default_sort_fields' => ['updatedAt'],
            'crud_field_title_candidates' => ['name'],
            'crud_field_identity_candidates' => ['code'],
            'crud_field_email_keywords' => ['email'],
            'crud_field_url_keywords' => ['url'],
            'crud_field_long_text_keywords' => ['description'],
            'crud_field_type_overrides' => [],
            'admin_enabled' => true,
            'admin_route_prefix' => '/manage',
            'admin_allowed_environments' => ['dev'],
            'admin_required_role' => 'ROLE_ADMIN',
            'admin_show_security_notes' => true,
            'admin_logout_path' => 'app_logout',
            'admin_logout_label' => 'Sign out',
        ];
    }
}
