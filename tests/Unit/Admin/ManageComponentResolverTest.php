<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Service\Admin\ManageComponentResolver;
use App\Managing\Service\Admin\ManageComponentTokenParser;
use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;
use App\Managing\Value\ManageCrudResourceDefinition;
use PHPUnit\Framework\TestCase;

final class ManageComponentResolverTest extends TestCase
{
    public function testResolverKeepsExactComponentKey(): void
    {
        $resolver = new ManageComponentResolver($this->createRegistry(), new ManageComponentTokenParser());

        self::assertSame('cataloging', $resolver->resolve('cataloging'));
        self::assertSame('accessing', $resolver->resolve('/manage/accessing'));
    }

    public function testResolverResolvesUniquePrefixAlias(): void
    {
        $resolver = new ManageComponentResolver($this->createRegistry(), new ManageComponentTokenParser());

        self::assertSame('cataloging', $resolver->resolve('catalog'));
        self::assertSame('accessing', $resolver->resolve('access'));
    }

    public function testResolverRejectsUnknownComponentToken(): void
    {
        $resolver = new ManageComponentResolver($this->createRegistry(), new ManageComponentTokenParser());

        self::assertSame('', $resolver->resolve('unknown'));
    }

    private function createRegistry(): ManageAdminRegistryInterface
    {
        return new class implements ManageAdminRegistryInterface {
            public function addProvider(\App\Managing\ServiceInterface\Admin\ManageAdminProviderInterface $provider): void
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
                        componentKey: 'accessing',
                        resourceKey: 'access_account_entity',
                        label: 'Access account',
                        entityClass: 'App\\Accessing\\Entity\\AccessAccountEntity',
                    ),
                    new ManageCrudResourceDefinition(
                        componentKey: 'cataloging',
                        resourceKey: 'catalog_category_entity',
                        label: 'Catalog category',
                        entityClass: 'App\\Cataloging\\Entity\\Catalog\\CatalogCategoryEntity',
                    ),
                ];
            }
        };
    }
}
