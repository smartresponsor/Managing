<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Security;

interface ManageAdminAccessPolicyInterface
{
    /**
     * @return array{enabled: bool, route_prefix: string, allowed_environments: array<int, string>, current_environment: string, environment_allowed: bool, required_role: string, show_security_notes: bool, logout_path: string, logout_label: string, enforcement: array<int, string>, warnings: array<int, string>}
     */
    public function describe(): array;

    public function isEnabled(): bool;

    public function isEnvironmentAllowed(): bool;

    public function getRoutePrefix(): string;

    public function getLogoutPath(): string;

    public function getLogoutLabel(): string;
}
