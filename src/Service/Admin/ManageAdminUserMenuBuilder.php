<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminUserMenuBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use Symfony\Component\Security\Core\User\UserInterface;

final readonly class ManageAdminUserMenuBuilder implements ManageAdminUserMenuBuilderInterface
{
    public function __construct(
        private string $logoutPath,
        private string $logoutLabel,
    ) {
    }

    public function buildUserMenu(UserInterface $user): UserMenu
    {
        $menu = UserMenu::new()
            ->setName($this->resolveUserName($user))
            ->displayUserName(true)
            ->displayUserAvatar(true)
            ->addMenuItems([
                MenuItem::linkToUrl('Switch account', 'fa fa-right-left', '/switch-account'),
                MenuItem::linkToUrl($this->resolveLogoutLabel(), 'fa fa-sign-out-alt', $this->resolveLogoutPath()),
            ]);

        return $menu;
    }

    private function resolveUserName(UserInterface $user): string
    {
        $identifier = trim($user->getUserIdentifier());

        return '' !== $identifier ? $identifier : 'Managing user';
    }

    private function resolveLogoutPath(): string
    {
        $path = trim($this->logoutPath);

        if ('' === $path) {
            return '/logout';
        }

        return str_starts_with($path, '/') ? $path : '/'.$path;
    }

    private function resolveLogoutLabel(): string
    {
        $label = trim($this->logoutLabel);

        return '' !== $label ? $label : 'Logout';
    }
}
