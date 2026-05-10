<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Form;

interface ManageFormDetailBuilderInterface
{
    /**
     * @return array<string, mixed>|null
     */
    public function buildFormDetail(string $componentKey, string $formKey): ?array;
}
