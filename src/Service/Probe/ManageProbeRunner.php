<?php

declare(strict_types=1);

namespace App\Managing\Service\Probe;

use App\Managing\ServiceInterface\Probe\ManageProbeRunnerInterface;
use App\Managing\Value\ManageProbeDefinition;
use App\Managing\Value\ManageProbeResult;

final readonly class ManageProbeRunner implements ManageProbeRunnerInterface
{
    public function __construct(private string $projectDir)
    {
    }

    public function runProbe(ManageProbeDefinition $probe): ManageProbeResult
    {
        if (!$probe->enabled) {
            return new ManageProbeResult(
                componentKey: $probe->componentKey,
                probeKey: $probe->probeKey,
                status: ManageProbeResult::STATUS_SKIPPED,
                messages: ['Probe is disabled.'],
            );
        }

        if ('managing' === $probe->componentKey && 'manage_canon_guard' === $probe->probeKey) {
            return $this->runManageCanonGuardProbe($probe);
        }

        return new ManageProbeResult(
            componentKey: $probe->componentKey,
            probeKey: $probe->probeKey,
            status: ManageProbeResult::STATUS_SKIPPED,
            messages: ['No native Manage runner is registered for this probe yet.'],
        );
    }

    private function runManageCanonGuardProbe(ManageProbeDefinition $probe): ManageProbeResult
    {
        $messages = [];
        $status = ManageProbeResult::STATUS_PASSED;
        $guardPath = $this->projectDir.'/tools/manage-canon-guard.php';

        if (!is_file($guardPath)) {
            $status = ManageProbeResult::STATUS_FAILED;
            $messages[] = 'Missing tools/manage-canon-guard.php.';
        } else {
            $messages[] = 'Canon guard file exists.';
        }

        $dashboardPath = $this->projectDir.'/src/Controller/Admin/ManageDashboardController.php';
        if (!is_file($dashboardPath)) {
            $status = ManageProbeResult::STATUS_FAILED;
            $messages[] = 'Missing ManageDashboardController.';
        } else {
            $dashboardSource = (string) file_get_contents($dashboardPath);
            if (!str_contains($dashboardSource, "#[Route('/manage'")) {
                $status = ManageProbeResult::STATUS_FAILED;
                $messages[] = 'ManageDashboardController does not declare /manage route.';
            } else {
                $messages[] = 'ManageDashboardController declares /manage route.';
            }
        }

        $servicesPath = $this->projectDir.'/config/services.yaml';
        if (!is_file($servicesPath)) {
            $status = ManageProbeResult::STATUS_FAILED;
            $messages[] = 'Missing config/services.yaml.';
        } else {
            $services = (string) file_get_contents($servicesPath);
            if (!str_contains($services, 'manage.admin_provider')) {
                $status = ManageProbeResult::STATUS_FAILED;
                $messages[] = 'No manage.admin_provider wiring found.';
            } else {
                $messages[] = 'manage.admin_provider wiring is present.';
            }
        }

        if ([] === $messages) {
            $messages[] = 'No probe checks were executed.';
        }

        return new ManageProbeResult(
            componentKey: $probe->componentKey,
            probeKey: $probe->probeKey,
            status: $status,
            messages: $messages,
        );
    }
}
