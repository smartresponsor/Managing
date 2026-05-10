<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Admin;

interface ManageMenuBuilderInterface
{
    /**
     * @return iterable<object>
     */
    public function buildMenuItems(): iterable;
}
