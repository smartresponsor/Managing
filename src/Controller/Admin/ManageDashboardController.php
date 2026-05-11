<?php

declare(strict_types=1);

namespace App\Managing\Controller\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminNavigationBuilderInterface;
use App\Managing\ServiceInterface\Admin\ManageAdminUserMenuBuilderInterface;
use App\Managing\ServiceInterface\Admin\ManageDashboardSummaryBuilderInterface;
use App\Managing\ServiceInterface\Admin\ManageMenuBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsController]
#[AdminDashboard(routePath: '/manage', routeName: 'manage_dashboard')]
final class ManageDashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly ManageDashboardSummaryBuilderInterface $summaryBuilder,
        private readonly ManageAdminNavigationBuilderInterface $navigationBuilder,
        private readonly ManageMenuBuilderInterface $menuBuilder,
        private readonly ManageAdminUserMenuBuilderInterface $userMenuBuilder,
    ) {
    }

    public function index(): Response
    {
        $summary = $this->summaryBuilder->buildSummary();

        return $this->render('@EasyAdmin/page/content.html.twig', [
            'page_title' => 'Managing admin',
            'content_title' => 'Managing admin',
            'content' => $this->renderView('manage/admin/dashboard_summary.html.twig', [
                'summary' => $summary,
                'navigation' => $this->navigationBuilder->buildPrimaryNavigation(),
            ]),
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
        return $this->userMenuBuilder->buildUserMenu($user);
    }
}
