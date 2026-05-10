<?php

declare(strict_types=1);

namespace App\Managing\Service\Relation;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageContributionFilterInterface;
use App\Managing\ServiceInterface\Relation\ManageRelationRegistryInterface;

final readonly class ManageRelationRegistry implements ManageRelationRegistryInterface
{
    public function __construct(
        private ManageAdminRegistryInterface $adminRegistry,
        private ManageContributionFilterInterface $contributionFilter,
    ) {
    }

    public function getRelations(): array
    {
        $relations = [];

        foreach ($this->adminRegistry->getProviders() as $provider) {
            foreach ($provider->getRelations() as $relation) {
                if ($this->contributionFilter->isRelationEnabled($relation)) {
                    $relations[] = $relation;
                }
            }
        }

        return $relations;
    }
}
