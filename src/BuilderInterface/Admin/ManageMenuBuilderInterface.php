<?php

declare(strict_types=1);

namespace App\Managing\BuilderInterface\Admin;

interface ManageMenuBuilderInterface
{
    /**
     * @return iterable<object>
     */
    public function buildMenuItems(): iterable;
}
