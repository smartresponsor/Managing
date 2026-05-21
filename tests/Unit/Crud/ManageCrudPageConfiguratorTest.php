<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Service\Crud\ManageCrudPageConfigurator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use PHPUnit\Framework\TestCase;

final class ManageCrudPageConfiguratorTest extends TestCase
{
    public function testPageConfiguratorAppliesSharedCrudDefaults(): void
    {
        $crud = (new ManageCrudPageConfigurator())->configure(
            Crud::new(),
            'Article',
            'Articles',
            ['title', 'slug'],
            ['publishedAt' => 'DESC'],
            false,
        );

        self::assertSame(['title', 'slug'], $crud->getAsDto()->getSearchFields());
        self::assertSame(['publishedAt' => 'DESC'], $crud->getAsDto()->getDefaultSort());
        // EasyAdmin's public DTO accessors differ by version; search/default sort cover the stable contract here.
    }
}
