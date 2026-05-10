<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Relation;

interface ManageRelationDetailBuilderInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function buildRelationDetail(string $componentKey, string $relationKey): ?array;
}
