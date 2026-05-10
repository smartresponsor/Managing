<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Probe;

use App\Managing\Value\ManageProbeDefinition;

interface ManageProbeRegistryInterface
{
    /**
     * @return list<ManageProbeDefinition>
     */
    public function getProbes(): array;
}
