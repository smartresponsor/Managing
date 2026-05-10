<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use App\Managing\Controller\Admin\ManageDashboardController;
use App\Managing\ServiceInterface\Crud\ManageCrudActionUrlBuilderInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudingRouteBridgeInterface;
use App\Managing\Value\ManageCrudResourceDefinition;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class ManageCrudActionUrlBuilder implements ManageCrudActionUrlBuilderInterface
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
        private UrlGeneratorInterface $urlGenerator,
        private ManageCrudingRouteBridgeInterface $crudingRouteBridge,
    ) {
    }

    public function buildActionUrls(ManageCrudResourceDefinition $resource): array
    {
        if (!$resource->enabled) {
            return [];
        }

        if (ManageCrudResourceDefinition::MODE_EASYADMIN === $resource->mode && null !== $resource->crudControllerClass) {
            return [
                'index' => $this->easyAdminUrl($resource->crudControllerClass, Action::INDEX),
                'new' => $this->easyAdminUrl($resource->crudControllerClass, Action::NEW),
            ];
        }

        if (ManageCrudResourceDefinition::MODE_CRUDING_LINK === $resource->mode) {
            return $this->crudingRouteBridge->buildActionUrls($resource);
        }

        if (ManageCrudResourceDefinition::MODE_CUSTOM_ROUTE === $resource->mode && null !== $resource->routeNamePattern) {
            try {
                return ['open' => $this->urlGenerator->generate($resource->routeNamePattern)];
            } catch (RouteNotFoundException) {
                return [];
            }
        }

        return [];
    }

    private function easyAdminUrl(string $crudControllerClass, string $action): string
    {
        return $this->adminUrlGenerator
            ->setDashboard(ManageDashboardController::class)
            ->unsetAll()
            ->setController($crudControllerClass)
            ->setAction($action)
            ->generateUrl();
    }
}
