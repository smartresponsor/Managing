<?php

declare(strict_types=1);

namespace App\Managing\Controller\Admin;

use App\Managing\ServiceInterface\Diagnostics\ManageAdminDiagnosticsBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ManageStatusController extends AbstractController
{
    public function __construct(
        private readonly ManageAdminDiagnosticsBuilderInterface $diagnosticsBuilder,
    ) {
    }

    #[Route('/manage/status', name: 'manage_status', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('manage/admin/page.html.twig', [
            'page_title' => 'Manage status',
            'body_template' => 'manage/admin/status.html.twig',
            'body_context' => [
                'diagnostics' => $this->diagnosticsBuilder->buildDiagnostics(),
            ],
        ]);
    }
}
