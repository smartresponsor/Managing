<?php

declare(strict_types=1);

namespace App\Managing\BuilderInterface\Crud;

use App\Managing\Value\ManageCrudResourceDefinition;

interface ManageCrudActionUrlBuilderInterface
{
    /**
     * @return array<string, string>
     */
    public function buildActionUrls(ManageCrudResourceDefinition $resource): array;
}
