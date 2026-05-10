<?php

declare(strict_types=1);

namespace App\Managing\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
#[AdminDashboard(routePath: '/manage/', routeName: 'manage_dashboard')]
final class ManageDashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('manage/page/content.html.twig', [
            'page_title' => 'Managing admin',
            'content_title' => 'Managing admin',
            'content' => $this->renderView('manage/admin/dashboard.html.twig'),
        ]);
    }
}
