<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Admin;

interface ManageComponentDetailBuilderInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function buildComponentDetail(string $componentKey): ?array;
}
