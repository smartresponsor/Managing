<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use App\Managing\Controller\Admin\ManageDashboardController;
use App\Managing\ServiceInterface\Crud\ManageCrudActionUrlBuilderInterface;
use App\Managing\Value\ManageCrudResourceDefinition;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

final readonly class ManageCrudActionUrlBuilder implements ManageCrudActionUrlBuilderInterface
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator)
    {
    }

    public function buildActionUrls(ManageCrudResourceDefinition $resource): array
    {
        if (!$resource->enabled || 'manage' !== $resource->surface) {
            return [];
        }

        if (ManageCrudResourceDefinition::MODE_EASYADMIN === $resource->mode && null !== $resource->crudControllerClass) {
            return [
                'index' => $this->easyAdminUrl($resource->crudControllerClass, Action::INDEX),
                'new' => $this->easyAdminUrl($resource->crudControllerClass, Action::NEW),
            ];
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
