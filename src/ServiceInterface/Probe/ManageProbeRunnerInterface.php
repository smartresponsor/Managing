<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Probe;

use App\Managing\Value\ManageProbeDefinition;
use App\Managing\Value\ManageProbeResult;

interface ManageProbeRunnerInterface
{
    public function runProbe(ManageProbeDefinition $probe): ManageProbeResult;
}
