<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud;

use App\Managing\Service\Crud\ManageCrudActionConfigurator;
use App\Managing\Service\Crud\ManageCrudEntitySurfaceResolver;
use App\Managing\Service\Crud\ManageCrudFieldFactory;
use App\Managing\Service\Crud\ManageCrudFilterConfigurator;
use App\Managing\Service\Crud\ManageCrudPageConfigurator;
use App\Managing\Service\Crud\ManageCrudPublicationWorkflow;
use App\Managing\Service\Crud\ManageEntityInstantiator;
use App\Managing\Service\Crud\ManageEntityReflectionInspector;
use App\Managing\Service\Crud\ManagePublicationFieldStateAccessor;
use App\Managing\Service\Crud\ManagePublicationStateHandler;

/**
 * Runtime service holder for AbstractManageContentCrudController.
 *
 * The base CRUD controller keeps setter-injection hooks for Symfony services and
 * manual construction support for generated-controller tests. This holder owns
 * the lazy fallback graph so the controller itself can stay focused on the
 * EasyAdmin action flow rather than on service construction details.
 */
final class ManageCrudControllerRuntime
{
    private ?ManageCrudActionConfigurator $actionConfigurator = null;
    private ?ManageCrudEntitySurfaceResolver $entitySurfaceResolver = null;
    private ?ManageEntityReflectionInspector $entityInspector = null;
    private ?ManageCrudFieldFactory $fieldFactory = null;
    private ?ManageCrudFilterConfigurator $filterConfigurator = null;
    private ?ManageCrudPageConfigurator $pageConfigurator = null;
    private ?ManageCrudPublicationWorkflow $publicationWorkflow = null;
    private ?ManageEntityInstantiator $entityInstantiator = null;

    public function setActionConfigurator(ManageCrudActionConfigurator $actionConfigurator): void
    {
        $this->actionConfigurator = $actionConfigurator;
    }

    public function setEntitySurfaceResolver(ManageCrudEntitySurfaceResolver $entitySurfaceResolver): void
    {
        $this->entitySurfaceResolver = $entitySurfaceResolver;
    }

    public function setFieldFactory(ManageCrudFieldFactory $fieldFactory): void
    {
        $this->fieldFactory = $fieldFactory;
    }

    public function setFilterConfigurator(ManageCrudFilterConfigurator $filterConfigurator): void
    {
        $this->filterConfigurator = $filterConfigurator;
    }

    public function setPageConfigurator(ManageCrudPageConfigurator $pageConfigurator): void
    {
        $this->pageConfigurator = $pageConfigurator;
    }

    public function setPublicationWorkflow(ManageCrudPublicationWorkflow $publicationWorkflow): void
    {
        $this->publicationWorkflow = $publicationWorkflow;
    }

    public function setEntityInstantiator(ManageEntityInstantiator $entityInstantiator): void
    {
        $this->entityInstantiator = $entityInstantiator;
    }

    public function actionConfigurator(): ManageCrudActionConfigurator
    {
        return $this->actionConfigurator ??= new ManageCrudActionConfigurator();
    }

    public function entitySurfaceResolver(): ManageCrudEntitySurfaceResolver
    {
        return $this->entitySurfaceResolver ??= new ManageCrudEntitySurfaceResolver(
            entityInspector: $this->entityInspector(),
        );
    }

    public function entityInspector(): ManageEntityReflectionInspector
    {
        return $this->entityInspector ??= new ManageEntityReflectionInspector();
    }

    public function fieldFactory(): ManageCrudFieldFactory
    {
        return $this->fieldFactory ??= new ManageCrudFieldFactory(
            inspector: $this->entityInspector(),
        );
    }

    public function filterConfigurator(): ManageCrudFilterConfigurator
    {
        return $this->filterConfigurator ??= new ManageCrudFilterConfigurator();
    }

    public function pageConfigurator(): ManageCrudPageConfigurator
    {
        return $this->pageConfigurator ??= new ManageCrudPageConfigurator();
    }

    public function publicationWorkflow(): ManageCrudPublicationWorkflow
    {
        return $this->publicationWorkflow ??= new ManageCrudPublicationWorkflow(
            new ManagePublicationStateHandler(new ManagePublicationFieldStateAccessor($this->entityInspector())),
        );
    }

    public function entityInstantiator(): ManageEntityInstantiator
    {
        return $this->entityInstantiator ??= new ManageEntityInstantiator();
    }
}
