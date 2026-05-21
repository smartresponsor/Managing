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
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Runtime holder and setter-injection seam for generated Manage CRUD controllers.
 *
 * Generated controllers may still be instantiated manually by unit tests, while
 * Symfony can inject configured services when the controller is managed by the
 * container. Keeping this seam outside the abstract CRUD controller prevents the
 * controller body from becoming a service-construction hub.
 */
trait ManageCrudControllerRuntimeInjectionTrait
{
    private ?ManageCrudControllerRuntime $manageCrudRuntime = null;

    #[Required]
    public function setManageCrudActionConfigurator(ManageCrudActionConfigurator $manageCrudActionConfigurator): void
    {
        $this->crudRuntime()->setActionConfigurator($manageCrudActionConfigurator);
    }

    #[Required]
    public function setManageCrudEntitySurfaceResolver(ManageCrudEntitySurfaceResolver $manageCrudEntitySurfaceResolver): void
    {
        $this->crudRuntime()->setEntitySurfaceResolver($manageCrudEntitySurfaceResolver);
    }

    #[Required]
    public function setManageCrudFieldFactory(ManageCrudFieldFactory $manageCrudFieldFactory): void
    {
        $this->crudRuntime()->setFieldFactory($manageCrudFieldFactory);
    }

    #[Required]
    public function setManageCrudFilterConfigurator(ManageCrudFilterConfigurator $manageCrudFilterConfigurator): void
    {
        $this->crudRuntime()->setFilterConfigurator($manageCrudFilterConfigurator);
    }

    #[Required]
    public function setManageCrudPageConfigurator(ManageCrudPageConfigurator $manageCrudPageConfigurator): void
    {
        $this->crudRuntime()->setPageConfigurator($manageCrudPageConfigurator);
    }

    #[Required]
    public function setManageCrudPublicationWorkflow(ManageCrudPublicationWorkflow $manageCrudPublicationWorkflow): void
    {
        $this->crudRuntime()->setPublicationWorkflow($manageCrudPublicationWorkflow);
    }

    #[Required]
    public function setManageEntityInstantiator(ManageEntityInstantiator $manageEntityInstantiator): void
    {
        $this->crudRuntime()->setEntityInstantiator($manageEntityInstantiator);
    }

    private function actionConfigurator(): ManageCrudActionConfigurator
    {
        return $this->crudRuntime()->actionConfigurator();
    }

    private function entitySurfaceResolver(): ManageCrudEntitySurfaceResolver
    {
        return $this->crudRuntime()->entitySurfaceResolver();
    }

    private function fieldFactory(): ManageCrudFieldFactory
    {
        return $this->crudRuntime()->fieldFactory();
    }

    private function filterConfigurator(): ManageCrudFilterConfigurator
    {
        return $this->crudRuntime()->filterConfigurator();
    }

    private function pageConfigurator(): ManageCrudPageConfigurator
    {
        return $this->crudRuntime()->pageConfigurator();
    }

    private function publicationWorkflow(): ManageCrudPublicationWorkflow
    {
        return $this->crudRuntime()->publicationWorkflow();
    }

    private function entityInstantiator(): ManageEntityInstantiator
    {
        return $this->crudRuntime()->entityInstantiator();
    }

    private function crudRuntime(): ManageCrudControllerRuntime
    {
        return $this->manageCrudRuntime ??= new ManageCrudControllerRuntime();
    }
}
