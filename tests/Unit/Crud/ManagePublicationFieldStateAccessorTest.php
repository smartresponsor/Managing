<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Service\Crud\ManagePublicationFieldStateAccessor;
use PHPUnit\Framework\TestCase;

final class ManagePublicationFieldStateAccessorTest extends TestCase
{
    public function testSupportsAndMutatesBooleanPublicationFlag(): void
    {
        $accessor = new ManagePublicationFieldStateAccessor();
        $entity = new ManagePublicationFieldStateAccessorFlagProbe();

        self::assertTrue($accessor->supports(ManagePublicationFieldStateAccessorFlagProbe::class, ['published'], []));
        self::assertFalse($accessor->isPublished(ManagePublicationFieldStateAccessorFlagProbe::class, $entity, ['published'], []));

        $accessor->writePublishedState(ManagePublicationFieldStateAccessorFlagProbe::class, $entity, true, ['published'], []);

        self::assertTrue($accessor->isPublished(ManagePublicationFieldStateAccessorFlagProbe::class, $entity, ['published'], []));
    }

    public function testUsesPublicationDateWhenFlagIsUnavailable(): void
    {
        $accessor = new ManagePublicationFieldStateAccessor();
        $entity = new ManagePublicationFieldStateAccessorDateProbe();

        self::assertTrue($accessor->supports(ManagePublicationFieldStateAccessorDateProbe::class, [], ['publishedAt']));
        self::assertFalse($accessor->isPublished(ManagePublicationFieldStateAccessorDateProbe::class, $entity, [], ['publishedAt']));

        $accessor->writePublishedState(ManagePublicationFieldStateAccessorDateProbe::class, $entity, true, [], ['publishedAt']);

        self::assertTrue($accessor->isPublished(ManagePublicationFieldStateAccessorDateProbe::class, $entity, [], ['publishedAt']));
    }
}

final class ManagePublicationFieldStateAccessorFlagProbe
{
    private bool $published = false;
}

final class ManagePublicationFieldStateAccessorDateProbe
{
    private ?\DateTimeImmutable $publishedAt = null;
}
