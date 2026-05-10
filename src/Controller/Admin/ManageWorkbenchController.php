<?php

declare(strict_types=1);

namespace App\Managing\Controller\Admin;

use App\Managing\ServiceInterface\Workbench\ManageWorkbenchIndexBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ManageWorkbenchController extends AbstractController
{
    public function __construct(
        private readonly ManageWorkbenchIndexBuilderInterface $workbenchIndexBuilder,
    ) {
    }

    #[Route('/manage/workbench', name: 'manage_workbench', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('manage/page/content.html.twig', [
            'page_title' => 'Manage workbench index',
            'content_title' => 'Manage workbench index',
            'content' => $this->renderView('manage/admin/workbench.html.twig', [
                'index' => $this->workbenchIndexBuilder->buildIndex(),
            ]),
        ]);
    }
}
