<?php

declare(strict_types=1);

namespace App\Managing\Service\Security;

use App\Managing\ServiceInterface\Security\ManageAdminAccessPolicyInterface;

final readonly class ManageAdminAccessPolicy implements ManageAdminAccessPolicyInterface
{
    /**
     * @param array<int, string> $allowedEnvironments
     */
    public function __construct(
        private bool $enabled,
        private string $routePrefix,
        private array $allowedEnvironments,
        private string $requiredRole,
        private bool $showSecurityNotes,
        private string $currentEnvironment,
    ) {
    }

    /**
     * @return array{enabled: bool, route_prefix: string, allowed_environments: array<int, string>, current_environment: string, environment_allowed: bool, required_role: string, show_security_notes: bool, enforcement: array<int, string>, warnings: array<int, string>}
     */
    public function describe(): array
    {
        $warnings = [];

        if ($this->enabled && [] !== $this->allowedEnvironments && !$this->isEnvironmentAllowed()) {
            $warnings[] = sprintf('Current environment "%s" is not listed in admin_allowed_environments.', $this->currentEnvironment);
        }

        if ($this->enabled && '' === $this->requiredRole) {
            $warnings[] = 'admin_required_role is empty; host access_control should still protect /manage.';
        }

        return [
            'enabled' => $this->enabled,
            'route_prefix' => $this->getRoutePrefix(),
            'allowed_environments' => $this->allowedEnvironments,
            'current_environment' => $this->currentEnvironment,
            'environment_allowed' => $this->isEnvironmentAllowed(),
            'required_role' => $this->requiredRole,
            'show_security_notes' => $this->showSecurityNotes,
            'enforcement' => [
                'This bundle enforces admin_enabled and admin_allowed_environments for the configured route prefix.',
                'Role checks remain host-owned and should be configured through the host security access_control for /manage.',
            ],
            'warnings' => $warnings,
        ];
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isEnvironmentAllowed(): bool
    {
        return [] === $this->allowedEnvironments || in_array($this->currentEnvironment, $this->allowedEnvironments, true);
    }

    public function getRoutePrefix(): string
    {
        $prefix = trim($this->routePrefix);

        if ('' === $prefix) {
            return '/manage';
        }

        return str_starts_with($prefix, '/') ? rtrim($prefix, '/') : '/'.rtrim($prefix, '/');
    }
}
