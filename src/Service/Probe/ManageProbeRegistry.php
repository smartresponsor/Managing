<?php

declare(strict_types=1);

namespace App\Managing\Service\Probe;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageContributionFilterInterface;
use App\Managing\ServiceInterface\Probe\ManageProbeRegistryInterface;

final readonly class ManageProbeRegistry implements ManageProbeRegistryInterface
{
    public function __construct(
        private ManageAdminRegistryInterface $adminRegistry,
        private ManageContributionFilterInterface $contributionFilter,
    ) {
    }

    public function getProbes(): array
    {
        $probes = [];

        foreach ($this->adminRegistry->getProviders() as $provider) {
            foreach ($provider->getProbes() as $probe) {
                if ($this->contributionFilter->isProbeEnabled($probe)) {
                    $probes[] = $probe;
                }
            }
        }

        return $probes;
    }
}
