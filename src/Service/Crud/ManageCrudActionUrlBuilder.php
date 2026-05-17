<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use App\Managing\Controller\Admin\ManageDashboardController;
use App\Managing\ServiceInterface\Crud\ManageCrudActionUrlBuilderInterface;
use App\Managing\Value\ManageCrudResourceDefinition;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Routing\RouterInterface;

final readonly class ManageCrudActionUrlBuilder implements ManageCrudActionUrlBuilderInterface
{
    public function __construct(
        private AdminUrlGenerator $adminUrlGenerator,
        private RouterInterface $router,
    ) {
    }

    public function buildActionUrls(ManageCrudResourceDefinition $resource): array
    {
        if (!$resource->enabled || 'manage' !== $resource->surface) {
            return [];
        }

        if (ManageCrudResourceDefinition::MODE_EASYADMIN === $resource->mode && null !== $resource->crudControllerClass) {
            $indexUrl = $this->easyAdminUrl($resource->crudControllerClass, Action::INDEX);
            $newUrl = $this->easyAdminUrl($resource->crudControllerClass, Action::NEW);

            $urls = [];
            if ('' !== $indexUrl) {
                $urls['index'] = $indexUrl;
            }
            if ('' !== $newUrl) {
                $urls['new'] = $newUrl;
            }

            return $urls;
        }

        return [];
    }

    private function easyAdminUrl(string $crudControllerClass, string $action): string
    {
        $url = $this->adminUrlGenerator
            ->setDashboard(ManageDashboardController::class)
            ->unsetAll()
            ->setController($crudControllerClass)
            ->setAction($action)
            ->generateUrl();

        return $this->routeExists($url) ? $url : '';
    }

    private function routeExists(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || '' === $path) {
            return false;
        }

        try {
            $this->router->match($path);
        } catch (\Throwable) {
            return false;
        }

        return true;
    }
}
