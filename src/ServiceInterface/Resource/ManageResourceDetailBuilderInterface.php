<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Resource;

interface ManageResourceDetailBuilderInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function buildResourceDetail(string $componentKey, string $resourceKey): ?array;
}
