<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Diagnostics;

interface ManageAdminDiagnosticsBuilderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function buildDiagnostics(): array;
}
