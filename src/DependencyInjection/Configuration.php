<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('managing');
        $children = $treeBuilder->getRootNode()->children();

        foreach (self::scalarListNodes() as $name => $default) {
            ManagingConfigurationNodeBuilder::scalarList($children, $name, $default);
        }

        foreach (self::scalarMapNodes() as $name => [$keyName, $default]) {
            ManagingConfigurationNodeBuilder::scalarMap($children, $name, $keyName, $default);
        }

        foreach (self::scalarListMapNodes() as $name => [$keyName, $default]) {
            ManagingConfigurationNodeBuilder::scalarListMap($children, $name, $keyName, $default);
        }

        foreach (self::intMapMapNodes() as $name => [$outerKeyName, $innerKeyName, $default]) {
            ManagingConfigurationNodeBuilder::intMapMap($children, $name, $outerKeyName, $innerKeyName, $default);
        }

        foreach (self::scalarMapMapNodes() as $name => [$outerKeyName, $innerKeyName, $default]) {
            ManagingConfigurationNodeBuilder::scalarMapMap($children, $name, $outerKeyName, $innerKeyName, $default);
        }

        foreach (self::booleanNodes() as $name => $default) {
            ManagingConfigurationNodeBuilder::boolean($children, $name, $default);
        }

        foreach (self::scalarNodes() as $name => $default) {
            ManagingConfigurationNodeBuilder::scalar($children, $name, $default);
        }

        $children->end();

        return $treeBuilder;
    }

    /** @return array<string, list<string>> */
    private static function scalarListNodes(): array
    {
        return [
            'enabled_components' => [],
            'disabled_resources' => [],
            'menu_order' => [],
            'left_menu' => ManagingConfigurationDefaults::leftMenu(),
            'menu_excluded_components' => ManagingConfigurationDefaults::menuExcludedComponents(),
            'host_scan_source_roots' => ManagingConfigurationDefaults::hostScanSourceRoots(),
            'host_scan_namespace_prefixes' => ManagingConfigurationDefaults::hostScanNamespacePrefixes(),
            'host_scan_excluded_namespaces' => ManagingConfigurationDefaults::hostScanExcludedNamespaces(),
            'crud_primary_technical_keywords' => ManagingConfigurationDefaults::crudPrimaryTechnicalKeywords(),
            'crud_primary_business_keywords' => ManagingConfigurationDefaults::crudPrimaryBusinessKeywords(),
            'crud_generated_attachment_migration_components' => ManagingConfigurationDefaults::crudGeneratedAttachmentMigrationComponents(),
            'crud_behavior_search_fields' => ManagingConfigurationDefaults::crudBehaviorSearchFields(),
            'crud_behavior_status_fields' => ManagingConfigurationDefaults::crudBehaviorStatusFields(),
            'crud_behavior_publication_flag_fields' => ManagingConfigurationDefaults::crudBehaviorPublicationFlagFields(),
            'crud_behavior_publication_date_fields' => ManagingConfigurationDefaults::crudBehaviorPublicationDateFields(),
            'crud_behavior_audit_date_fields' => ManagingConfigurationDefaults::crudBehaviorAuditDateFields(),
            'crud_behavior_default_sort_fields' => ManagingConfigurationDefaults::crudBehaviorDefaultSortFields(),
            'crud_field_title_candidates' => ManagingConfigurationDefaults::crudFieldTitleCandidates(),
            'crud_field_identity_candidates' => ManagingConfigurationDefaults::crudFieldIdentityCandidates(),
            'crud_field_email_keywords' => ManagingConfigurationDefaults::crudFieldEmailKeywords(),
            'crud_field_url_keywords' => ManagingConfigurationDefaults::crudFieldUrlKeywords(),
            'crud_field_long_text_keywords' => ManagingConfigurationDefaults::crudFieldLongTextKeywords(),
            'admin_allowed_environments' => [],
        ];
    }

    /** @return array<string, array{0: string, 1: array<string, string>}> */
    private static function scalarMapNodes(): array
    {
        return [
            'component_root_names' => ['component', ManagingConfigurationDefaults::componentRootNames()],
            'component_root_aliases' => ['root_name', ManagingConfigurationDefaults::componentRootAliases()],
        ];
    }

    /** @return array<string, array{0: string, 1: array<string, list<string>>}> */
    private static function scalarListMapNodes(): array
    {
        return [
            'host_scan_included_entity_suffixes_by_component' => ['component', ManagingConfigurationDefaults::hostScanIncludedEntitySuffixesByComponent()],
        ];
    }

    /** @return array<string, array{0: string, 1: string, 2: array<string, array<string, int>>}> */
    private static function intMapMapNodes(): array
    {
        return [
            'crud_primary_entity_bonus_suffixes_by_component' => ['component', 'suffix', ManagingConfigurationDefaults::crudPrimaryEntityBonusSuffixesByComponent()],
            'crud_primary_entity_penalty_suffixes_by_component' => ['component', 'suffix', ManagingConfigurationDefaults::crudPrimaryEntityPenaltySuffixesByComponent()],
        ];
    }

    /** @return array<string, array{0: string, 1: string, 2: array<string, array<string, string>>}> */
    private static function scalarMapMapNodes(): array
    {
        return [
            'crud_field_type_overrides' => ['entity', 'field', []],
        ];
    }

    /** @return array<string, bool> */
    private static function booleanNodes(): array
    {
        return [
            'host_scan_enabled' => true,
            'admin_enabled' => true,
            'admin_show_security_notes' => true,
        ];
    }

    /** @return array<string, string> */
    private static function scalarNodes(): array
    {
        return [
            'admin_route_prefix' => '/manage',
            'admin_required_role' => 'ROLE_ADMIN',
            'admin_logout_path' => '/logout',
            'admin_logout_label' => 'Logout',
        ];
    }
}
