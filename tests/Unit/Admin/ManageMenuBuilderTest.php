<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Service\Admin\ManageMenuBuilder;
use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\Value\ManageComponentDefinition;
use PHPUnit\Framework\TestCase;

final class ManageMenuBuilderTest extends TestCase
{
    public function testMenuBuilderKeepsOnlyAllowlistedComponentsInConfiguredOrder(): void
    {
        $registry = new class implements ManageAdminRegistryInterface {
            public function addProvider(\App\Managing\ServiceInterface\Admin\ManageAdminProviderInterface $provider): void
            {
            }

            public function getProviders(): array
            {
                return [];
            }

            public function getComponents(): array
            {
                return [
                    new ManageComponentDefinition('host_application', 'Host application'),
                    new ManageComponentDefinition('billing', 'Billing'),
                    new ManageComponentDefinition('bridge', 'Bridge'),
                    new ManageComponentDefinition('accessing', 'Accessing'),
                    new ManageComponentDefinition('vendoring', 'Vendoring'),
                ];
            }

            public function getCrudResources(): array
            {
                return [];
            }
        };

        $builder = new ManageMenuBuilder($registry, ['accessing', 'billing', 'vendoring']);

        $items = iterator_to_array($builder->buildMenuItems(), false);

        self::assertCount(3, $items);
        self::assertSame(['Accessing', 'Billing', 'Vendoring'], array_map(
            static fn ($item): string => (string) $item->getAsDto()->getLabel(),
            $items
        ));
        self::assertSame(['manage_dashboard_components_detail', 'manage_dashboard_components_detail', 'manage_dashboard_components_detail'], array_map(
            static fn ($item): string => $item->getAsDto()->getRouteName(),
            $items
        ));
        self::assertSame(['accessing', 'billing', 'vendoring'], array_map(
            static fn ($item): string => $item->getAsDto()->getRouteParameters()['componentKey'],
            $items
        ));
    }
}
