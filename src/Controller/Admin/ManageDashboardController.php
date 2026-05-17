<?php

declare(strict_types=1);

namespace App\Managing\Controller\Admin;

use App\Managing\ServiceInterface\Admin\ManageMenuBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsController]
#[AdminDashboard(routePath: '/manage', routeName: 'manage')]
final class ManageDashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly ManageMenuBuilderInterface $menuBuilder,
    ) {
    }

    public function index(): Response
    {
        return $this->render('@EasyAdmin/page/content.html.twig', [
            'content_title' => 'Manage',
            'page_title' => 'Manage',
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Managing')
            ->setFaviconPath('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><text y="50" font-size="48">M</text></svg>');
    }

    public function configureMenuItems(): iterable
    {
        yield from $this->menuBuilder->buildMenuItems();
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return UserMenu::new()
            ->setName($this->resolveUserName($user))
            ->displayUserName(true)
            ->displayUserAvatar(true);
    }

    private function resolveUserName(UserInterface $user): string
    {
        $identifier = trim($user->getUserIdentifier());

        return '' !== $identifier ? $identifier : 'Managing user';
    }
}
