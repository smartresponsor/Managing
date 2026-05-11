<?php

declare(strict_types=1);

namespace App\Managing\Controller\Admin;

use App\Managing\ServiceInterface\Crud\ManageCrudActionUrlBuilderInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudResourceRegistryInterface;
use App\Managing\ServiceInterface\Resource\ManageResourceDetailBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ManageResourceController extends AbstractController
{
    public function __construct(
        private readonly ManageCrudResourceRegistryInterface $crudResourceRegistry,
        private readonly ManageCrudActionUrlBuilderInterface $actionUrlBuilder,
        private readonly ManageResourceDetailBuilderInterface $resourceDetailBuilder,
    ) {
    }

    #[Route('/manage/resources', name: 'manage_resources', methods: ['GET'])]
    public function index(): Response
    {
        $rows = [];

        foreach ($this->crudResourceRegistry->getCrudResources() as $resource) {
            $rows[] = [
                'resource' => $resource,
                'actions' => $this->actionUrlBuilder->buildActionUrls($resource),
            ];
        }

        return $this->render('@EasyAdmin/page/content.html.twig', [
            'page_title' => 'Manage resources',
            'content_title' => 'Manage resources',
            'content' => $this->renderView('manage/admin/resources.html.twig', [
                'rows' => $rows,
            ]),
        ]);
    }

    #[Route('/manage/resources/{componentKey}/{resourceKey}', name: 'manage_resource_detail', methods: ['GET'])]
    public function detail(string $componentKey, string $resourceKey): Response
    {
        $detail = $this->resourceDetailBuilder->buildResourceDetail($componentKey, $resourceKey);

        if (null === $detail) {
            throw $this->createNotFoundException(sprintf('Manage resource "%s.%s" was not found.', $componentKey, $resourceKey));
        }

        return $this->render('@EasyAdmin/page/content.html.twig', [
            'page_title' => 'Manage resource detail',
            'content_title' => 'Manage resource detail',
            'content' => $this->renderView('manage/admin/resource_detail.html.twig', $detail),
        ]);
    }
}
