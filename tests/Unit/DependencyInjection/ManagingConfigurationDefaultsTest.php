<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\DependencyInjection;

use App\Managing\DependencyInjection\ManagingConfigurationDefaults;
use App\Managing\DependencyInjection\ManagingCrudConfigurationDefaults;
use App\Managing\DependencyInjection\ManagingHostConfigurationDefaults;
use App\Managing\DependencyInjection\ManagingMenuConfigurationDefaults;
use PHPUnit\Framework\TestCase;

final class ManagingConfigurationDefaultsTest extends TestCase
{
    public function testFacadeDelegatesMenuDefaults(): void
    {
        self::assertSame(ManagingMenuConfigurationDefaults::leftMenu(), ManagingConfigurationDefaults::leftMenu());
        self::assertSame(
            ManagingMenuConfigurationDefaults::excludedComponents(),
            ManagingConfigurationDefaults::menuExcludedComponents(),
        );
    }

    public function testComponentRootsPreserveKnownCanonicalMappings(): void
    {
        $roots = ManagingHostConfigurationDefaults::componentRootNames();

        self::assertSame('catalog', $roots['cataloging']);
        self::assertSame('message', $roots['messaging']);
        self::assertSame('page', $roots['paging']);
        self::assertSame($roots, ManagingConfigurationDefaults::componentRootNames());
    }

    public function testRootAliasesPreserveCatalogCategoryResolution(): void
    {
        self::assertSame(['category' => 'cataloging'], ManagingHostConfigurationDefaults::componentRootAliases());
        self::assertSame(
            ManagingHostConfigurationDefaults::componentRootAliases(),
            ManagingConfigurationDefaults::componentRootAliases(),
        );
    }

    public function testCrudBehaviorDefaultsRemainStable(): void
    {
        self::assertContains('firstTitle', ManagingCrudConfigurationDefaults::behaviorSearchFields());
        self::assertContains('published', ManagingCrudConfigurationDefaults::behaviorPublicationFlagFields());
        self::assertContains('updatedAt', ManagingCrudConfigurationDefaults::behaviorDefaultSortFields());
        self::assertSame(
            ManagingCrudConfigurationDefaults::behaviorSearchFields(),
            ManagingConfigurationDefaults::crudBehaviorSearchFields(),
        );
    }
}
