<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Service\Admin\Host\ManageHostClassNameResolver;
use App\Managing\Service\Admin\ManageCrudResourcePolicy;
use PHPUnit\Framework\TestCase;

final class ManageHostClassNameResolverTest extends TestCase
{
    public function testRootEntityComponentKeyComesFromResourcePolicy(): void
    {
        $resolver = new ManageHostClassNameResolver(new ManageCrudResourcePolicy(
            componentRootNames: ['messaging' => 'message'],
            componentRootAliases: ['category' => 'cataloging'],
        ));

        self::assertSame('messaging', $resolver->componentKeyFromClass('App\Entity\Message\Message'));
        self::assertSame('cataloging', $resolver->componentKeyFromClass('App\Entity\Category\Category'));
        self::assertSame('unknown', $resolver->componentKeyFromClass('App\Entity\Unknown\Unknown'));
    }
}
