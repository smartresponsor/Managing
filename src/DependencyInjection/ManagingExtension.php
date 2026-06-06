<?php

declare(strict_types=1);

namespace App\Managing\DependencyInjection;

use App\Managing\Filter\Admin\ManageContributionFilter;
use App\Managing\Generator\Admin\ManageCrudControllerGenerator;
use App\Managing\Policy\Admin\ManageCrudResourcePolicy;
use App\Managing\Provider\Admin\Host\ManageHostApplicationAdminProvider;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

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

        $this->configureFieldUserProfileBackendAliases($container, $config);
        (new ManagingFieldExternalAccessBackendConfigurator())->configure($container, $config);
        $this->configureFieldUserProfileDoctrineStorage($container, $config);
    }

    /** @param array<string, mixed> $config */
    private function configureFieldUserProfileDoctrineStorage(ContainerBuilder $container, array $config): void
    {
        if ('doctrine' !== $config['crud_field_user_profile_reader_backend']
            && 'doctrine' !== $config['crud_field_user_profile_writer_backend']) {
            return;
        }

        $entityManagerService = trim((string) $config['crud_field_user_profile_entity_manager_service']);
        if ('' === $entityManagerService) {
            throw new \InvalidArgumentException('managing.crud_field_user_profile_entity_manager_service must not be empty when doctrine profile storage is enabled.');
        }

        $repositoryId = 'App\\Managing\\Repository\\Crud\\ManageDoctrineCrudFieldViewProfileRuleRepository';
        if ($container->hasDefinition($repositoryId)) {
            $container->getDefinition($repositoryId)->setArgument('$entityManager', new Reference($entityManagerService));
        }
    }

    /** @param array<string, mixed> $config */
    private function configureFieldUserProfileBackendAliases(ContainerBuilder $container, array $config): void
    {
        $this->configureAlias(
            $container,
            'App\\Managing\\ResolverInterface\\Crud\\ManageCrudFieldUserProfileResolverInterface',
            (string) $config['crud_field_user_profile_runtime_backend'],
            [
                'none' => 'App\\Managing\\Resolver\\Crud\\ManageCrudNullFieldUserProfileResolver',
                'config' => 'App\\Managing\\Resolver\\Crud\\ManageCrudConfiguredFieldUserProfileResolver',
                'reader' => 'App\\Managing\\Resolver\\Crud\\ManageCrudReaderFieldUserProfileResolver',
            ],
            'crud_field_user_profile_runtime_backend',
        );

        $this->configureAlias(
            $container,
            'App\\Managing\\ReaderInterface\\Crud\\ManageCrudFieldUserProfileReaderInterface',
            (string) $config['crud_field_user_profile_reader_backend'],
            [
                'none' => 'App\\Managing\\Reader\\Crud\\ManageCrudNullFieldUserProfileReader',
                'config' => 'App\\Managing\\Reader\\Crud\\ManageCrudConfigShapeFieldUserProfileReader',
                'doctrine' => 'App\\Managing\\Reader\\Crud\\ManageCrudDoctrineFieldUserProfileReader',
            ],
            'crud_field_user_profile_reader_backend',
        );

        $this->configureAlias(
            $container,
            'App\\Managing\\WriterInterface\\Crud\\ManageCrudFieldUserProfileWriterInterface',
            (string) $config['crud_field_user_profile_writer_backend'],
            [
                'none' => 'App\\Managing\\Writer\\Crud\\ManageCrudNullFieldUserProfileWriter',
                'config' => 'App\\Managing\\Writer\\Crud\\ManageCrudConfigShapeFieldUserProfileWriter',
                'doctrine' => 'App\\Managing\\Writer\\Crud\\ManageCrudDoctrineFieldUserProfileWriter',
            ],
            'crud_field_user_profile_writer_backend',
        );
    }

    /** @param array<string, string> $serviceByBackend */
    private function configureAlias(
        ContainerBuilder $container,
        string $interfaceId,
        string $backend,
        array $serviceByBackend,
        string $configKey,
    ): void {
        $backend = trim($backend);
        $serviceId = $serviceByBackend[$backend] ?? null;
        if (null === $serviceId) {
            throw new \InvalidArgumentException(sprintf('Unsupported managing.%s backend "%s". Allowed values: %s.', $configKey, $backend, implode(', ', array_keys($serviceByBackend))));
        }

        $container->setAlias($interfaceId, $serviceId);
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
