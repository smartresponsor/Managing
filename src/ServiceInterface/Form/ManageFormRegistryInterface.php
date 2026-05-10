<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Form;

use App\Managing\Value\ManageFormDefinition;

interface ManageFormRegistryInterface
{
    /**
     * @return list<ManageFormDefinition>
     */
    public function getForms(): array;
}
