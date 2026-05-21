<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Service\Crud\ManageBackedEnumChoiceBuilder;
use PHPUnit\Framework\TestCase;

enum ManageWave7StatusFixture: string
{
    case Draft = 'draft';
    case ReadyForReview = 'ready_for_review';
}

final class ManageBackedEnumChoiceBuilderTest extends TestCase
{
    public function testBuildsHumanReadableBackedEnumChoices(): void
    {
        $builder = new ManageBackedEnumChoiceBuilder();

        self::assertSame(
            [
                'Draft' => ManageWave7StatusFixture::Draft,
                'Ready for review' => ManageWave7StatusFixture::ReadyForReview,
            ],
            $builder->choicesFor(ManageWave7StatusFixture::class)
        );
    }

    public function testReturnsEmptyChoicesForNonEnumType(): void
    {
        $builder = new ManageBackedEnumChoiceBuilder();

        self::assertSame([], $builder->choicesFor(self::class));
    }
}
