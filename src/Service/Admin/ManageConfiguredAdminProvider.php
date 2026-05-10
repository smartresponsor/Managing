<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminProviderInterface;
use App\Managing\Value\ManageComponentDefinition;
use App\Managing\Value\ManageCrudResourceDefinition;
use App\Managing\Value\ManageFormDefinition;
use App\Managing\Value\ManageProbeDefinition;
use App\Managing\Value\ManageRelationDefinition;
use App\Managing\Value\ManageRouteDefinition;

final readonly class ManageConfiguredAdminProvider implements ManageAdminProviderInterface
{
    /**
     * @param array<string, array<string, mixed>> $components
     * @param list<array<string, mixed>>          $resources
     * @param list<array<string, mixed>>          $routes
     * @param list<array<string, mixed>>          $forms
     * @param list<array<string, mixed>>          $relations
     * @param list<array<string, mixed>>          $probes
     */
    public function __construct(
        private array $components = [],
        private array $resources = [],
        private array $routes = [],
        private array $forms = [],
        private array $relations = [],
        private array $probes = [],
    ) {
    }

    public function getComponent(): ManageComponentDefinition
    {
        return new ManageComponentDefinition(
            key: 'configured',
            label: 'Configured resources',
            description: 'Host-defined Manage contributions loaded from managing.yaml.',
        );
    }

    public function getCrudResources(): iterable
    {
        foreach ($this->resources as $resource) {
            yield new ManageCrudResourceDefinition(
                componentKey: $this->stringValue($resource, 'component', 'configured'),
                resourceKey: $this->stringValue($resource, 'key', 'configured_resource'),
                label: $this->stringValue($resource, 'label', $this->stringValue($resource, 'key', 'Configured resource')),
                entityClass: $this->stringValue($resource, 'entity_class', ''),
                crudControllerClass: $this->nullableStringValue($resource, 'crud_controller_class'),
                formTypeClass: $this->nullableStringValue($resource, 'form_type_class'),
                routeNamePattern: $this->nullableStringValue($resource, 'route_name_pattern'),
                menuGroup: $this->nullableStringValue($resource, 'menu_group'),
                enabled: $this->boolValue($resource, 'enabled', true),
                mode: $this->stringValue($resource, 'mode', ManageCrudResourceDefinition::MODE_CUSTOM_ROUTE),
                resourcePath: $this->nullableStringValue($resource, 'resource_path'),
                identifierField: $this->stringValue($resource, 'identifier_field', 'id'),
                surface: $this->stringValue($resource, 'surface', 'admin'),
                templatePrefix: $this->stringValue($resource, 'template_prefix', 'crud'),
            );
        }
    }

    public function getRoutes(): iterable
    {
        foreach ($this->routes as $route) {
            yield new ManageRouteDefinition(
                componentKey: $this->stringValue($route, 'component', 'configured'),
                routeName: $this->stringValue($route, 'route_name', ''),
                label: $this->stringValue($route, 'label', $this->stringValue($route, 'route_name', 'Configured route')),
                kind: $this->stringValue($route, 'kind', 'configured'),
                menuGroup: $this->nullableStringValue($route, 'menu_group'),
                enabled: $this->boolValue($route, 'enabled', true),
            );
        }
    }

    public function getForms(): iterable
    {
        foreach ($this->forms as $form) {
            yield new ManageFormDefinition(
                componentKey: $this->stringValue($form, 'component', 'configured'),
                formKey: $this->stringValue($form, 'key', 'configured_form'),
                label: $this->stringValue($form, 'label', $this->stringValue($form, 'key', 'Configured form')),
                formTypeClass: $this->stringValue($form, 'form_type_class', ''),
                resourceKey: $this->nullableStringValue($form, 'resource_key'),
                description: $this->nullableStringValue($form, 'description'),
                menuGroup: $this->nullableStringValue($form, 'menu_group'),
                enabled: $this->boolValue($form, 'enabled', true),
                surface: $this->stringValue($form, 'surface', 'admin'),
                mode: $this->stringValue($form, 'mode', 'symfony_form'),
            );
        }
    }

    public function getProbes(): iterable
    {
        foreach ($this->probes as $probe) {
            yield new ManageProbeDefinition(
                componentKey: $this->stringValue($probe, 'component', 'configured'),
                probeKey: $this->stringValue($probe, 'key', 'configured_probe'),
                label: $this->stringValue($probe, 'label', $this->stringValue($probe, 'key', 'Configured probe')),
                description: $this->nullableStringValue($probe, 'description'),
                enabled: $this->boolValue($probe, 'enabled', true),
            );
        }
    }

    public function getRelations(): iterable
    {
        foreach ($this->relations as $relation) {
            yield new ManageRelationDefinition(
                componentKey: $this->stringValue($relation, 'component', 'configured'),
                relationKey: $this->stringValue($relation, 'key', 'configured_relation'),
                label: $this->stringValue($relation, 'label', $this->stringValue($relation, 'key', 'Configured relation')),
                sourceResourceKey: $this->stringValue($relation, 'source_resource_key', ''),
                targetResourceKey: $this->stringValue($relation, 'target_resource_key', ''),
                kind: $this->stringValue($relation, 'kind', 'association'),
                sourceField: $this->nullableStringValue($relation, 'source_field'),
                targetField: $this->nullableStringValue($relation, 'target_field'),
                description: $this->nullableStringValue($relation, 'description'),
                menuGroup: $this->nullableStringValue($relation, 'menu_group'),
                enabled: $this->boolValue($relation, 'enabled', true),
                surface: $this->stringValue($relation, 'surface', 'admin'),
            );
        }
    }

    /**
     * @return iterable<ManageComponentDefinition>
     */
    public function getConfiguredComponents(): iterable
    {
        foreach ($this->components as $key => $component) {
            yield new ManageComponentDefinition(
                key: (string) $key,
                label: $this->stringValue($component, 'label', (string) $key),
                description: $this->nullableStringValue($component, 'description'),
            );
        }
    }

    /** @param array<string, mixed> $values */
    private function stringValue(array $values, string $key, string $default): string
    {
        $value = $values[$key] ?? $default;

        return is_scalar($value) ? (string) $value : $default;
    }

    /** @param array<string, mixed> $values */
    private function nullableStringValue(array $values, string $key): ?string
    {
        $value = $values[$key] ?? null;

        return is_scalar($value) && '' !== $value ? (string) $value : null;
    }

    /** @param array<string, mixed> $values */
    private function boolValue(array $values, string $key, bool $default): bool
    {
        $value = $values[$key] ?? $default;

        return is_bool($value) ? $value : $default;
    }
}
