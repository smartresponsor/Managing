<?php

declare(strict_types=1);

namespace App\Managing\Controller\Admin;

use App\Managing\ServiceInterface\Route\ManageRouteDetailBuilderInterface;
use App\Managing\ServiceInterface\Route\ManageRouteRegistryInterface;
use App\Managing\ServiceInterface\Route\ManageRouteUrlResolverInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class ManageRouteController extends AbstractController
{
    public function __construct(
        private readonly ManageRouteRegistryInterface $routeRegistry,
        private readonly ManageRouteUrlResolverInterface $routeUrlResolver,
        private readonly ManageRouteDetailBuilderInterface $routeDetailBuilder,
    ) {
    }

    #[Route('/manage/routes', name: 'manage_routes', methods: ['GET'])]
    public function index(): Response
    {
        $rows = [];

        foreach ($this->routeRegistry->getRoutes() as $route) {
            $rows[] = [
                'route' => $route,
                'url' => $this->routeUrlResolver->resolveUrl($route),
                'detailUrl' => $this->generateUrl('manage_route_detail', [
                    'componentKey' => $route->componentKey,
                    'routeName' => $route->routeName,
                ]),
            ];
        }

        return $this->render('manage/admin/page.html.twig', [
            'page_title' => 'Manage routes',
            'body_template' => 'manage/admin/routes.html.twig',
            'body_context' => [
                'rows' => $rows,
            ],
        ]);
    }

    #[Route('/manage/routes/{componentKey}/{routeName}', name: 'manage_route_detail', methods: ['GET'])]
    public function detail(string $componentKey, string $routeName): Response
    {
        $detail = $this->routeDetailBuilder->buildRouteDetail($componentKey, $routeName);

        if (null === $detail) {
            throw $this->createNotFoundException(sprintf('Manage route "%s/%s" was not found.', $componentKey, $routeName));
        }

        return $this->render('manage/admin/page.html.twig', [
            'page_title' => 'Manage route detail',
            'body_template' => 'manage/admin/route_detail.html.twig',
            'body_context' => $detail,
        ]);
    }
}
