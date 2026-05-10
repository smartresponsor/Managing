<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Relation;

use App\Managing\Value\ManageRelationDefinition;

interface ManageRelationRegistryInterface
{
    /**
     * @return list<ManageRelationDefinition>
     */
    public function getRelations(): array;
}
