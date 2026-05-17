<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Admin;

use App\Managing\Service\Admin\ManageMenuBuilder;
use PHPUnit\Framework\TestCase;

final class ManageMenuBuilderTest extends TestCase
{
    public function testMenuBuilderPublishesOneItemPerComponent(): void
    {
        $builder = new ManageMenuBuilder(['cataloging'], ['managing']);

        $items = iterator_to_array($builder->buildMenuItems(), false);

        self::assertCount(2, $items);
        self::assertSame(['Manage', 'Cataloging'], array_map(
            static fn ($item): string => (string) $item->getAsDto()->getLabel(),
            $items
        ));
        self::assertSame('/manage/cataloging', $items[1]->getAsDto()->getLinkUrl());
    }
}
