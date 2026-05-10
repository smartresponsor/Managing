<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Probe;

interface ManageProbeDetailBuilderInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function buildProbeDetail(string $componentKey, string $probeKey): ?array;
}
