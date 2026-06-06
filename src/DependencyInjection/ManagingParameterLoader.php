<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

final class ManagingParameterLoader
{
    /**
     * @var array<string, string>
     */
    private const CONFIG_TO_PARAMETER = [
        'enabled_components' => 'managing.enabled_components',
        'disabled_resources' => 'managing.disabled_resources',
        'menu_order' => 'managing.menu_order',
        'left_menu' => 'managing.left_menu',
        'menu_excluded_components' => 'managing.menu_excluded_components',
        'host_scan_enabled' => 'managing.host_scan_enabled',
        'host_scan_source_roots' => 'managing.host_scan_source_roots',
        'host_scan_namespace_prefixes' => 'managing.host_scan_namespace_prefixes',
        'host_scan_excluded_namespaces' => 'managing.host_scan_excluded_namespaces',
        'component_root_names' => 'managing.component_root_names',
        'component_root_aliases' => 'managing.component_root_aliases',
        'host_scan_included_entity_suffixes_by_component' => 'managing.host_scan_included_entity_suffixes_by_component',
        'crud_primary_entity_bonus_suffixes_by_component' => 'managing.crud_primary_entity_bonus_suffixes_by_component',
        'crud_primary_entity_penalty_suffixes_by_component' => 'managing.crud_primary_entity_penalty_suffixes_by_component',
        'crud_primary_technical_keywords' => 'managing.crud_primary_technical_keywords',
        'crud_primary_business_keywords' => 'managing.crud_primary_business_keywords',
        'crud_generated_attachment_migration_components' => 'managing.crud_generated_attachment_migration_components',
        'crud_behavior_search_fields' => 'managing.crud_behavior_search_fields',
        'crud_behavior_status_fields' => 'managing.crud_behavior_status_fields',
        'crud_behavior_publication_flag_fields' => 'managing.crud_behavior_publication_flag_fields',
        'crud_behavior_publication_date_fields' => 'managing.crud_behavior_publication_date_fields',
        'crud_behavior_audit_date_fields' => 'managing.crud_behavior_audit_date_fields',
        'crud_behavior_default_sort_fields' => 'managing.crud_behavior_default_sort_fields',
        'crud_field_primary_identifier_candidates' => 'managing.crud_field_primary_identifier_candidates',
        'crud_field_title_candidates' => 'managing.crud_field_title_candidates',
        'crud_field_identity_candidates' => 'managing.crud_field_identity_candidates',
        'crud_field_description_candidates' => 'managing.crud_field_description_candidates',
        'crud_field_technical_excluded_fields' => 'managing.crud_field_technical_excluded_fields',
        'crud_field_visibility' => 'managing.crud_field_visibility',
        'crud_field_user_profiles' => 'managing.crud_field_user_profiles',
        'crud_field_user_profile_runtime_backend' => 'managing.crud_field_user_profile_runtime_backend',
        'crud_field_user_profile_reader_backend' => 'managing.crud_field_user_profile_reader_backend',
        'crud_field_user_profile_writer_backend' => 'managing.crud_field_user_profile_writer_backend',
        'crud_field_user_profile_entity_manager_service' => 'managing.crud_field_user_profile_entity_manager_service',
        'crud_field_external_access_backend' => 'managing.crud_field_external_access_backend',
        'crud_field_external_access_failure_effect' => 'managing.crud_field_external_access_failure_effect',
        'crud_field_external_access_rolling_decision_service' => 'managing.crud_field_external_access_rolling_decision_service',
        'crud_field_external_access_permission_key' => 'managing.crud_field_external_access_permission_key',
        'crud_field_email_keywords' => 'managing.crud_field_email_keywords',
        'crud_field_url_keywords' => 'managing.crud_field_url_keywords',
        'crud_field_long_text_keywords' => 'managing.crud_field_long_text_keywords',
        'crud_field_type_overrides' => 'managing.crud_field_type_overrides',
        'admin_enabled' => 'managing.admin_enabled',
        'admin_route_prefix' => 'managing.admin_route_prefix',
        'admin_allowed_environments' => 'managing.admin_allowed_environments',
        'admin_required_role' => 'managing.admin_required_role',
        'admin_show_security_notes' => 'managing.admin_show_security_notes',
        'admin_logout_path' => 'managing.admin_logout_path',
        'admin_logout_label' => 'managing.admin_logout_label',
    ];

    /**
     * @param array<string, mixed> $config
     */
    public function load(ContainerBuilder $container, array $config, string $bundleDir): void
    {
        $container->setParameter('managing.bundle_dir', $bundleDir);

        foreach (self::CONFIG_TO_PARAMETER as $configKey => $parameterName) {
            $container->setParameter($parameterName, $config[$configKey]);
        }
    }

    /**
     * @return array<string, string>
     */
    public static function parameterMap(): array
    {
        return self::CONFIG_TO_PARAMETER;
    }
}
