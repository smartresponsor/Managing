<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Service\Crud\ManageCrudFormFieldDiscovery;
use PHPUnit\Framework\TestCase;

final class ManageCrudFormFieldDiscoveryTest extends TestCase
{
    public function testUnknownEntityReturnsNoFields(): void
    {
        $discovery = new ManageCrudFormFieldDiscovery();

        self::assertSame([], $discovery->discover('App\\Managing\\Tests\\Unit\\Crud\\MissingEntity', 'new', [], [], []));
    }

    public function testNonFormPageReturnsNoFields(): void
    {
        $discovery = new ManageCrudFormFieldDiscovery();

        self::assertSame([], $discovery->discover(self::class, 'index', [], [], []));
    }
}
