<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Admin;

interface ManageConfigurationViewBuilderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function buildConfigurationView(): array;
}
