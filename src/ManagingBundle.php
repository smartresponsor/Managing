<?php

declare(strict_types=1);

namespace App\Managing;

use App\Managing\DependencyInjection\Compiler\ManageProviderPass;
use App\Managing\ServiceInterface\Admin\ManageAdminProviderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class ManagingBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->registerForAutoconfiguration(ManageAdminProviderInterface::class)
            ->addTag(ManageProviderPass::ADMIN_PROVIDER_TAG);

        $container->addCompilerPass(new ManageProviderPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
