<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Service\Admin\ManageMenuBuilder;
use App\Managing\ServiceInterface\Crud\ManageCrudResourceRegistryInterface;
use App\Managing\Value\ManageCrudResourceDefinition;
use PHPUnit\Framework\TestCase;

final class ManageMenuBuilderTest extends TestCase
{
    public function testMenuBuilderPublishesOneItemPerComponent(): void
    {
        $registry = new class implements ManageCrudResourceRegistryInterface {
            public function getCrudResources(): array
            {
                return [
                    new ManageCrudResourceDefinition(
                        componentKey: 'cataloging',
                        resourceKey: 'catalog',
                        label: 'Catalog',
                        entityClass: 'App\\Catalog\\Entity\\Catalog\\Catalog',
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
