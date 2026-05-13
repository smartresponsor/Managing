<?php

declare(strict_types=1);

namespace App\Managing\Controller\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageComponentDetailBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ManageComponentController extends AbstractController
{
    public function __construct(
        private readonly ManageAdminRegistryInterface $adminRegistry,
        private readonly ManageComponentDetailBuilderInterface $componentDetailBuilder,
    ) {
    }

    #[Route('/manage/components', name: 'manage_components', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('manage/admin/content.html.twig', [
            'page_title' => 'Manage components',
            'content_title' => 'Manage components',
            'content' => $this->renderView('manage/admin/components.html.twig', [
                'components' => $this->adminRegistry->getComponents(),
            ]),
        ]);
    }

    #[Route('/manage/components/{componentKey}', name: 'manage_component_detail', methods: ['GET'])]
    public function detail(string $componentKey): Response
    {
        $detail = $this->componentDetailBuilder->buildComponentDetail($componentKey);

        if (null === $detail) {
            throw $this->createNotFoundException(sprintf('Manage component "%s" was not found.', $componentKey));
        }

        return $this->render('manage/admin/content.html.twig', [
            'page_title' => 'Manage component detail',
            'content_title' => 'Manage component detail',
            'content' => $this->renderView('manage/admin/component_detail.html.twig', $detail),
        ]);
    }
}
