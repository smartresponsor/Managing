<?php

declare(strict_types=1);

namespace App\Managing\Provider\Administration;

use App\Managing\ServiceInterface\Administration\RollingBackedManagingAccessReadinessProviderInterface;
use App\Managing\Value\Administration\RollingBackedManagingAccessReadinessItem;
use App\Managing\Value\Administration\RollingBackedManagingAccessReadinessReport;

/**
 * Documents the activation corridor for Rolling-backed Managing field access.
 */
final readonly class RollingBackedManagingAccessReadinessProvider implements RollingBackedManagingAccessReadinessProviderInterface
{
    private const ROLLING_CONTRACT = 'App\\Rolling\\ServiceInterface\\Administration\\RollingFieldAccessDecisionServiceInterface';
    private const MANAGING_ADAPTER = 'App\\Managing\\Resolver\\Crud\\ManageCrudRollingFieldExternalAccessResolver';
    private const MANAGING_RESOLVER_CONTRACT = 'App\\Managing\\ResolverInterface\\Crud\\ManageCrudFieldExternalAccessResolverInterface';

    public function report(): RollingBackedManagingAccessReadinessReport
    {
        return new RollingBackedManagingAccessReadinessReport(
            mode: 'rolling',
            failureEffect: 'deny',
            permissionKey: 'managing.field.view',
            rollingDecisionContract: self::ROLLING_CONTRACT,
            managingAdapterContract: self::MANAGING_RESOLVER_CONTRACT,
            items: [
                $this->item('managing_backend', 'Managing external access backend', 'host_required', 'Managing host config', 'crud_field_external_access_backend: rolling', 'Must be enabled explicitly; default remains none.'),
                $this->item('failure_effect', 'Fail-closed production mode', 'recommended', 'Managing host config', 'crud_field_external_access_failure_effect: deny', 'Use abstain only for staged local integration.'),
                $this->item('rolling_contract', 'Rolling field decision service', 'contract_known', 'Rolling', self::ROLLING_CONTRACT, 'Rolling owns effective allow/deny/abstain decisions.'),
                $this->item('managing_adapter', 'Managing Rolling adapter', 'contract_known', 'Managing', self::MANAGING_ADAPTER, 'Adapter bridges Managing context into Rolling without EasyAdmin coupling.'),
                $this->item('permission_key', 'Field view permission key', 'catalog_required', 'Rolling/Administering', 'managing.field.view', 'Must exist in the Rolling catalog before production use.'),
                $this->item('admin_mutation', 'Admin review/apply corridor', 'available', 'Administering', 'field access mutation review/apply surfaces', 'Role/user/group policy changes stay controlled by Administering and Rolling.'),
                $this->item('diagnostics', 'Explainability and inspection', 'available', 'Managing/Administering', 'field visibility explanation + inspection surfaces', 'Operators can inspect why a field is visible, hidden, denied, or unavailable.'),
            ],
        );
    }

    private function item(string $key, string $label, string $status, string $owner, string $expectedValue, string $note): RollingBackedManagingAccessReadinessItem
    {
        return new RollingBackedManagingAccessReadinessItem($key, $label, $status, $owner, $expectedValue, $note);
    }
}
