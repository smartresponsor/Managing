<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Service\Crud\ManageCrudFieldLabeler;
use PHPUnit\Framework\TestCase;

final class ManageCrudFieldLabelerTest extends TestCase
{
    public function testCamelCaseFieldIsHumanized(): void
    {
        self::assertSame('First Title', (new ManageCrudFieldLabeler())->labelFor('firstTitle'));
    }

    public function testSnakeCaseFieldIsHumanized(): void
    {
        self::assertSame('External id', (new ManageCrudFieldLabeler())->labelFor('external_id'));
    }
}
