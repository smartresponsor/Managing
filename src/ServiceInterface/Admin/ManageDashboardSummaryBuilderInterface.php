<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Admin;

interface ManageDashboardSummaryBuilderInterface
{
    /**
     * @return array<string, int>
     */
    public function buildSummary(): array;
}
