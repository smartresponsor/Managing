<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Admin;

use App\Managing\Value\ManageComponentDefinition;
use App\Managing\Value\ManageCrudResourceDefinition;
use App\Managing\Value\ManageFormDefinition;
use App\Managing\Value\ManageProbeDefinition;
use App\Managing\Value\ManageRelationDefinition;
use App\Managing\Value\ManageRouteDefinition;

interface ManageAdminProviderInterface
{
    public function getComponent(): ManageComponentDefinition;

    /**
     * @return iterable<ManageCrudResourceDefinition>
     */
    public function getCrudResources(): iterable;

    /**
     * @return iterable<ManageRouteDefinition>
     */
    public function getRoutes(): iterable;

    /**
     * @return iterable<ManageFormDefinition>
     */
    public function getForms(): iterable;

    /**
     * @return iterable<ManageProbeDefinition>
     */
    public function getProbes(): iterable;

    /**
     * @return iterable<ManageRelationDefinition>
     */
    public function getRelations(): iterable;
}
