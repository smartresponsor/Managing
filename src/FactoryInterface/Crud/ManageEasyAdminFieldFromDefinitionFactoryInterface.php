<?php

declare(strict_types=1);

namespace App\Managing\FactoryInterface\Crud;

use App\Managing\Value\Crud\ManageCrudFieldDefinition;

interface ManageEasyAdminFieldFromDefinitionFactoryInterface
{
    public function fieldForDefinition(ManageCrudFieldDefinition $definition): ?object;
}
