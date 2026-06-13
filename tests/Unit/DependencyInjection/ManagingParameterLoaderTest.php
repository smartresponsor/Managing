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
        self::assertSame('managing.crud_field_primary_identifier_candidates', $map['crud_field_primary_identifier_candidates']);
        self::assertSame('managing.crud_field_description_candidates', $map['crud_field_description_candidates']);
        self::assertSame('managing.crud_field_type_overrides', $map['crud_field_type_overrides']);
        self::assertSame('managing.crud_field_visibility', $map['crud_field_visibility']);
        self::assertSame('managing.crud_field_user_profiles', $map['crud_field_user_profiles']);
        self::assertSame('managing.crud_field_user_profile_runtime_backend', $map['crud_field_user_profile_runtime_backend']);
        self::assertSame('managing.crud_field_user_profile_reader_backend', $map['crud_field_user_profile_reader_backend']);
        self::assertSame('managing.crud_field_user_profile_writer_backend', $map['crud_field_user_profile_writer_backend']);
        self::assertSame(
            'managing.crud_field_user_profile_entity_manager_service',
            $map['crud_field_user_profile_entity_manager_service'],
        );
        self::assertSame('managing.crud_field_external_access_backend', $map['crud_field_external_access_backend']);
        self::assertSame('managing.crud_field_external_access_failure_effect', $map['crud_field_external_access_failure_effect']);
        self::assertSame(
            'managing.crud_field_external_access_rolling_decision_service',
            $map['crud_field_external_access_rolling_decision_service'],
        );
        self::assertSame('managing.crud_field_external_access_permission_key', $map['crud_field_external_access_permission_key']);
        self::assertSame('managing.admin_logout_label', $map['admin_logout_label']);
    }

    public function testLoadPublishesProcessedConfigAsContainerParameters(): void
    {
        $container = new ContainerBuilder();
        $config = $this->minimalProcessedConfig();

        (new ManagingParameterLoader())->load($container, $config, '/tmp/managing');

        self::assertSame('/tmp/managing', $container->getParameter('managing.bundle_dir'));
        self::assertSame(['cataloging'], $container->getParameter('managing.left_menu'));
        self::assertSame(['CategoryEntity' => 'cataloging'], $container->getParameter('managing.component_root_aliases'));
        self::assertSame(['id'], $container->getParameter('managing.crud_field_primary_identifier_candidates'));
        self::assertSame(['description'], $container->getParameter('managing.crud_field_description_candidates'));
        self::assertSame(['email'], $container->getParameter('managing.crud_field_email_keywords'));
        self::assertSame(['defaults' => [], 'resources' => []], $container->getParameter('managing.crud_field_visibility'));
        self::assertSame(['subjects' => []], $container->getParameter('managing.crud_field_user_profiles'));
        self::assertSame('reader', $container->getParameter('managing.crud_field_user_profile_runtime_backend'));
        self::assertSame('doctrine', $container->getParameter('managing.crud_field_user_profile_reader_backend'));
        self::assertSame('none', $container->getParameter('managing.crud_field_user_profile_writer_backend'));
        self::assertSame(
            'doctrine.orm.system_entity_manager',
            $container->getParameter('managing.crud_field_user_profile_entity_manager_service'),
        );
        self::assertSame('rolling', $container->getParameter('managing.crud_field_external_access_backend'));
        self::assertSame('deny', $container->getParameter('managing.crud_field_external_access_failure_effect'));
        self::assertSame(
            'App\\Rolling\\ServiceInterface\\Administration\\RollingFieldAccessDecisionServiceInterface',
            $container->getParameter('managing.crud_field_external_access_rolling_decision_service'),
        );
        self::assertSame('managing.field.view', $container->getParameter('managing.crud_field_external_access_permission_key'));
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
            'component_root_aliases' => ['CategoryEntity' => 'cataloging'],
            'host_scan_included_entity_suffixes_by_component' => [],
            'crud_primary_entity_bonus_suffixes_by_component' => [],
            'crud_primary_entity_penalty_suffixes_by_component' => [],
            'crud_primary_technical_keywords' => [],
            'crud_primary_business_keywords' => [],
            'crud_generated_attachment_migration_components' => [],
            'crud_behavior_search_fields' => ['nameEntity'],
            'crud_behavior_status_fields' => ['status'],
            'crud_behavior_publication_flag_fields' => ['published'],
            'crud_behavior_publication_date_fields' => ['publishedAt'],
            'crud_behavior_audit_date_fields' => ['createdAt'],
            'crud_behavior_default_sort_fields' => ['updatedAt'],
            'crud_field_primary_identifier_candidates' => ['id'],
            'crud_field_title_candidates' => ['nameEntity'],
            'crud_field_identity_candidates' => ['code'],
            'crud_field_description_candidates' => ['description'],
            'crud_field_technical_excluded_fields' => [],
            'crud_field_visibility' => ['defaults' => [], 'resources' => []],
            'crud_field_user_profiles' => ['subjects' => []],
            'crud_field_user_profile_runtime_backend' => 'reader',
            'crud_field_user_profile_reader_backend' => 'doctrine',
            'crud_field_user_profile_writer_backend' => 'none',
            'crud_field_user_profile_entity_manager_service' => 'doctrine.orm.system_entity_manager',
            'crud_field_external_access_backend' => 'rolling',
            'crud_field_external_access_failure_effect' => 'deny',
            'crud_field_external_access_rolling_decision_service' => 'App\\Rolling\\ServiceInterface\\Administration\\RollingFieldAccessDecisionServiceInterface',
            'crud_field_external_access_permission_key' => 'managing.field.view',
            'crud_field_email_keywords' => ['email'],
            'crud_field_url_keywords' => ['url'],
            'crud_field_long_text_keywords' => ['description'],
            'crud_field_type_overrides' => [],
            'admin_enabled' => true,
            'admin_route_prefix' => '/ea',
            'admin_allowed_environments' => ['dev'],
            'admin_required_role' => 'ROLE_ADMIN',
            'admin_show_security_notes' => true,
            'admin_logout_path' => 'app_logout',
            'admin_logout_label' => 'Sign out',
        ];
    }
}
