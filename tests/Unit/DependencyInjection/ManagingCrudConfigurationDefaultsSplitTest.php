<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\DependencyInjection;

use App\Managing\DependencyInjection\ManagingCrudBehaviorConfigurationDefaults;
use App\Managing\DependencyInjection\ManagingCrudConfigurationDefaults;
use App\Managing\DependencyInjection\ManagingCrudFieldConfigurationDefaults;
use App\Managing\DependencyInjection\ManagingCrudResourceConfigurationDefaults;
use PHPUnit\Framework\TestCase;

final class ManagingCrudConfigurationDefaultsSplitTest extends TestCase
{
    public function testFacadeDelegatesToFocusedCrudDefaultProviders(): void
    {
        self::assertSame(
            ManagingCrudResourceConfigurationDefaults::primaryBusinessKeywords(),
            ManagingCrudConfigurationDefaults::primaryBusinessKeywords()
        );
        self::assertSame(
            ManagingCrudBehaviorConfigurationDefaults::searchFields(),
            ManagingCrudConfigurationDefaults::behaviorSearchFields()
        );
        self::assertSame(
            ManagingCrudFieldConfigurationDefaults::longTextKeywords(),
            ManagingCrudConfigurationDefaults::fieldLongTextKeywords()
        );
    }
}
