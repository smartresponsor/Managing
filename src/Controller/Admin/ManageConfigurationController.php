<?php

declare(strict_types=1);

namespace App\Managing\Controller\Admin;

use App\Managing\ServiceInterface\Admin\ManageConfigurationViewBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ManageConfigurationController extends AbstractController
{
    public function __construct(
        private readonly ManageConfigurationViewBuilderInterface $configurationViewBuilder,
    ) {
    }

    #[Route('/manage/configuration', name: 'manage_configuration', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('manage/admin/content.html.twig', [
            'page_title' => 'Manage configuration',
            'content_title' => 'Manage configuration',
            'content' => $this->renderView('manage/admin/configuration.html.twig', [
                'configuration' => $this->configurationViewBuilder->buildConfigurationView(),
            ]),
        ]);
    }
}
