<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageConfigurationViewBuilderInterface;

final readonly class ManageConfigurationViewBuilder implements ManageConfigurationViewBuilderInterface
{
    /**
     * @param array<int, string> $enabledComponents
     * @param array<int, string> $disabledResources
     * @param array<int, string> $menuOrder
     * @param array<int, string> $adminAllowedEnvironments
     * @param array<mixed>       $configuredComponents
     * @param array<mixed>       $configuredResources
     * @param array<mixed>       $configuredRoutes
     * @param array<mixed>       $configuredForms
     * @param array<mixed>       $configuredRelations
     * @param array<mixed>       $configuredProbes
     */
    public function __construct(
        private array $enabledComponents,
        private array $disabledResources,
        private array $menuOrder,
        private bool $adminEnabled,
        private string $adminRoutePrefix,
        private array $adminAllowedEnvironments,
        private string $adminRequiredRole,
        private bool $adminShowSecurityNotes,
        private string $adminLogoutPath,
        private string $adminLogoutLabel,
        private array $configuredComponents,
        private array $configuredResources,
        private array $configuredRoutes,
        private array $configuredForms,
        private array $configuredRelations,
        private array $configuredProbes,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function buildConfigurationView(): array
    {
        return [
            'enabled_components' => $this->enabledComponents,
            'disabled_resources' => $this->disabledResources,
            'menu_order' => $this->menuOrder,
            'admin_enabled' => $this->adminEnabled,
            'admin_route_prefix' => $this->adminRoutePrefix,
            'admin_allowed_environments' => $this->adminAllowedEnvironments,
            'admin_required_role' => $this->adminRequiredRole,
            'admin_show_security_notes' => $this->adminShowSecurityNotes,
            'admin_logout_path' => $this->adminLogoutPath,
            'admin_logout_label' => $this->adminLogoutLabel,
            'configured_components' => $this->summarizeConfiguredList($this->configuredComponents),
            'configured_resources' => $this->summarizeConfiguredList($this->configuredResources),
            'configured_routes' => $this->summarizeConfiguredList($this->configuredRoutes),
            'configured_forms' => $this->summarizeConfiguredList($this->configuredForms),
            'configured_relations' => $this->summarizeConfiguredList($this->configuredRelations),
            'configured_probes' => $this->summarizeConfiguredList($this->configuredProbes),
        ];
    }

    /**
     * @param array<mixed> $items
     *
     * @return array{count: int, keys: array<int, string>, raw: array<mixed>}
     */
    private function summarizeConfiguredList(array $items): array
    {
        $keys = [];

        foreach ($items as $index => $item) {
            if (is_array($item)) {
                $keys[] = $this->extractItemKey($item, (string) $index);
                continue;
            }

            $keys[] = (string) $index;
        }

        return [
            'count' => count($items),
            'keys' => $keys,
            'raw' => $items,
        ];
    }

    /**
     * @param array<mixed> $item
     */
    private function extractItemKey(array $item, string $fallback): string
    {
        foreach (['key', 'resource_key', 'route_name', 'form_key', 'relation_key', 'probe_key', 'component_key', 'label'] as $field) {
            if (isset($item[$field]) && is_scalar($item[$field])) {
                return (string) $item[$field];
            }
        }

        return $fallback;
    }
}
