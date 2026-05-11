<?php

declare(strict_types=1);

namespace App\Managing\ServiceInterface\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use Symfony\Component\Security\Core\User\UserInterface;

interface ManageAdminUserMenuBuilderInterface
{
    public function buildUserMenu(UserInterface $user): UserMenu;
}
