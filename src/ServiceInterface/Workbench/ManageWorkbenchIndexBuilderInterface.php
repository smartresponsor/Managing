<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Workbench;

interface ManageWorkbenchIndexBuilderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function buildIndex(): array;
}
