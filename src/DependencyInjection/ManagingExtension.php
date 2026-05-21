<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

use App\Managing\Service\Admin\ManageContributionFilter;
use App\Managing\Service\Admin\ManageCrudControllerGenerator;
use App\Managing\Service\Admin\ManageCrudResourcePolicy;
use App\Managing\Service\Admin\ManageHostApplicationAdminProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class ManagingExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @param array<int, array<string, mixed>> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $bundleDir = dirname(__DIR__, 2);
        (new ManagingParameterLoader())->load($container, $config, $bundleDir);

        $resourcePolicy = new ManageCrudResourcePolicy(
            componentRootNames: $config['component_root_names'],
            componentRootAliases: $config['component_root_aliases'],
            includedEntitySuffixesByComponent: $config['host_scan_included_entity_suffixes_by_component'],
            primaryEntityBonusSuffixesByComponent: $config['crud_primary_entity_bonus_suffixes_by_component'],
            primaryEntityPenaltySuffixesByComponent: $config['crud_primary_entity_penalty_suffixes_by_component'],
            technicalKeywords: $config['crud_primary_technical_keywords'],
            businessKeywords: $config['crud_primary_business_keywords'],
            componentsRequiringAttachmentIdentifierMigration: $config['crud_generated_attachment_migration_components'],
        );
        $hostProvider = new ManageHostApplicationAdminProvider(
            projectDir: (string) $container->getParameter('kernel.project_dir'),
            cacheDir: (string) $container->getParameter('kernel.cache_dir'),
            enabled: (bool) $config['host_scan_enabled'],
            sourceRoots: $config['host_scan_source_roots'],
            namespacePrefixes: $config['host_scan_namespace_prefixes'],
            excludedNamespaces: $config['host_scan_excluded_namespaces'],
            resourcePolicy: $resourcePolicy,
        );
        $contributionFilter = new ManageContributionFilter(
            enabledComponents: $config['enabled_components'],
            disabledResources: $config['disabled_resources'],
            menuOrder: $config['menu_order'],
        );
        $leftMenu = array_flip($config['left_menu']);
        $generator = new ManageCrudControllerGenerator($bundleDir, $resourcePolicy);
        $generator->synchronize(array_values(array_filter(
            iterator_to_array($hostProvider->getCrudResources(), false),
            static fn ($resource): bool => $contributionFilter->isCrudResourceEnabled($resource)
                && isset($leftMenu[$resource->componentKey]),
        )));

        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__, 2).'/config'));
        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('twig', [
            'paths' => [
                dirname(__DIR__, 2).'/templates',
            ],
        ]);
    }
}
