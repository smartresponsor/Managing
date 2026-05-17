<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Service\Admin\ManageMenuBuilder;
use App\Managing\ServiceInterface\Admin\ManageAdminProviderInterface;
use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\Value\ManageCrudResourceDefinition;
use PHPUnit\Framework\TestCase;

final class ManageMenuBuilderTest extends TestCase
{
    public function testMenuBuilderPublishesOnlySiblingCrudResources(): void
    {
        $registry = new class implements ManageAdminRegistryInterface {
            public function addProvider(ManageAdminProviderInterface $provider): void
            {
            }

            public function getProviders(): array
            {
                return [];
            }

            public function getCrudResources(): array
            {
                return [
                    new ManageCrudResourceDefinition(
                        componentKey: 'cataloging',
                        resourceKey: 'catalog_category_entity',
                        label: 'Catalog categories',
                        entityClass: 'App\\Cataloging\\Entity\\Catalog\\CatalogCategoryEntity',
                        crudControllerClass: 'App\\Cataloging\\Controller\\Crud\\CatalogCategoryCrudController',
                        mode: ManageCrudResourceDefinition::MODE_EASYADMIN,
                        surface: 'manage',
                    ),
                ];
            }
        };

        $builder = new ManageMenuBuilder($registry, ['cataloging'], ['managing']);

        $items = iterator_to_array($builder->buildMenuItems(), false);

        self::assertCount(2, $items);
        self::assertSame(['Manage', 'Cataloging'], array_map(
            static fn ($item): string => (string) $item->getAsDto()->getLabel(),
            $items
        ));
        self::assertSame('/manage/cataloging', $items[1]->getAsDto()->getLinkUrl());
    }
}
