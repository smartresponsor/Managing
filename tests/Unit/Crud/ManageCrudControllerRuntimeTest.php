<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Configurator\Crud\ManageCrudActionConfigurator;
use App\Managing\Configurator\Crud\ManageCrudFilterConfigurator;
use App\Managing\Configurator\Crud\ManageCrudPageConfigurator;
use App\Managing\Controller\Crud\ManageCrudControllerRuntime;
use App\Managing\Factory\Crud\ManageCrudFieldFactory;
use App\Managing\Instantiator\Crud\ManageEntityInstantiator;
use App\Managing\Resolver\Crud\ManageCrudEntitySurfaceResolver;
use App\Managing\Workflow\Crud\ManageCrudPublicationWorkflow;
use PHPUnit\Framework\TestCase;

final class ManageCrudControllerRuntimeTest extends TestCase
{
    public function testRuntimeProvidesLazyFallbackServices(): void
    {
        $runtime = new ManageCrudControllerRuntime();

        self::assertInstanceOf(ManageCrudActionConfigurator::class, $runtime->actionConfigurator());
        self::assertInstanceOf(ManageCrudEntitySurfaceResolver::class, $runtime->entitySurfaceResolver());
        self::assertInstanceOf(ManageCrudFieldFactory::class, $runtime->fieldFactory());
        self::assertInstanceOf(ManageCrudFilterConfigurator::class, $runtime->filterConfigurator());
        self::assertInstanceOf(ManageCrudPageConfigurator::class, $runtime->pageConfigurator());
        self::assertInstanceOf(ManageCrudPublicationWorkflow::class, $runtime->publicationWorkflow());
        self::assertInstanceOf(ManageEntityInstantiator::class, $runtime->entityInstantiator());
    }

    public function testRuntimeKeepsInjectedServices(): void
    {
        $runtime = new ManageCrudControllerRuntime();
        $actionConfigurator = new ManageCrudActionConfigurator();
        $entityInstantiator = new ManageEntityInstantiator();

        $runtime->setActionConfigurator($actionConfigurator);
        $runtime->setEntityInstantiator($entityInstantiator);

        self::assertSame($actionConfigurator, $runtime->actionConfigurator());
        self::assertSame($entityInstantiator, $runtime->entityInstantiator());
    }
}
