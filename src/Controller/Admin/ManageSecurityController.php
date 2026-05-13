<?php

declare(strict_types=1);

namespace App\Managing\Controller\Admin;

use App\Managing\ServiceInterface\Security\ManageAdminSecurityViewBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ManageSecurityController extends AbstractController
{
    public function __construct(private readonly ManageAdminSecurityViewBuilderInterface $securityViewBuilder)
    {
    }

    #[Route('/manage/security', name: 'manage_security')]
    public function index(): Response
    {
        return $this->render('manage/admin/content.html.twig', [
            'page_title' => 'Manage security',
            'content_title' => 'Manage security',
            'content' => $this->renderView('manage/admin/security.html.twig', [
                'security' => $this->securityViewBuilder->buildSecurityView(),
            ]),
        ]);
    }
}
