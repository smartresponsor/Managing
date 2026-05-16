<?php

declare(strict_types=1);

namespace App\Managing\Controller\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\ServiceInterface\Admin\ManageComponentDetailBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
#[AdminRoute(path: '/components', name: 'components')]
final class ManageComponentController extends AbstractController
{
    public function __construct(
        private readonly ManageAdminRegistryInterface $adminRegistry,
        private readonly ManageComponentDetailBuilderInterface $componentDetailBuilder,
    ) {
    }

    #[AdminRoute(path: '', name: 'index')]
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

    #[AdminRoute(path: '/{componentKey}', name: 'detail')]
    public function detail(string $componentKey): Response
    {
        $detail = $this->componentDetailBuilder->buildComponentDetail($componentKey);

        if (null === $detail) {
            throw $this->createNotFoundException(sprintf('Manage component "%s" was not found.', $componentKey));
        }

        return $this->render('manage/admin/content.html.twig', [
            'page_title' => sprintf('%s index', $detail['component']->label),
            'content_title' => sprintf('%s index', $detail['component']->label),
            'content' => $this->renderView('manage/admin/component_detail.html.twig', $detail),
        ]);
    }
}
