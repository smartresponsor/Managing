<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Controller\Crud\ManageCrudControllerRuntimeInjectionTrait;
use App\Managing\Service\Crud\ManageCrudActionConfigurator;
use App\Managing\Service\Crud\ManageEntityInstantiator;
use PHPUnit\Framework\TestCase;

final class ManageCrudControllerRuntimeInjectionTraitTest extends TestCase
{
    public function testTraitKeepsInjectedRuntimeCollaborators(): void
    {
        $probe = new ManageCrudControllerRuntimeInjectionProbe();
        $actionConfigurator = new ManageCrudActionConfigurator();
        $entityInstantiator = new ManageEntityInstantiator();

        $probe->setManageCrudActionConfigurator($actionConfigurator);
        $probe->setManageEntityInstantiator($entityInstantiator);

        self::assertSame($actionConfigurator, $probe->actionConfiguratorProbe());
        self::assertSame($entityInstantiator, $probe->entityInstantiatorProbe());
    }
}

final class ManageCrudControllerRuntimeInjectionProbe
{
    use ManageCrudControllerRuntimeInjectionTrait;

    public function actionConfiguratorProbe(): ManageCrudActionConfigurator
    {
        return $this->actionConfigurator();
    }

    public function entityInstantiatorProbe(): ManageEntityInstantiator
    {
        return $this->entityInstantiator();
    }
}
