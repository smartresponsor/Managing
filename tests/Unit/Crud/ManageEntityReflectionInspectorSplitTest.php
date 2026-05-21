<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Service\Crud\ManageEntityFieldAccessor;
use App\Managing\Service\Crud\ManageEntityMetadataInspector;
use App\Managing\Service\Crud\ManageEntityReflectionInspector;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\TestCase;

enum ManageWave27StatusFixture: string
{
    case Draft = 'draft';
}

final class ManageWave27EntityFixture
{
    #[ORM\Column(enumType: ManageWave27StatusFixture::class)]
    private ManageWave27StatusFixture $status = ManageWave27StatusFixture::Draft;

    private ?string $title = null;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }
}

final class ManageEntityReflectionInspectorSplitTest extends TestCase
{
    public function testFieldAccessorReadsAndWritesAccessorBackedFields(): void
    {
        $entity = new ManageWave27EntityFixture();
        $accessor = new ManageEntityFieldAccessor();

        self::assertTrue($accessor->hasField(ManageWave27EntityFixture::class, 'title'));
        self::assertTrue($accessor->writeField(ManageWave27EntityFixture::class, $entity, 'title', 'Wave 27'));
        self::assertSame('Wave 27', $accessor->readField($entity, 'title'));
        self::assertSame(['title'], $accessor->existingFields(ManageWave27EntityFixture::class, ['missing', 'title']));
    }

    public function testMetadataInspectorResolvesPropertyTypesAndEnumColumns(): void
    {
        $metadata = new ManageEntityMetadataInspector();
        $property = new \ReflectionProperty(ManageWave27EntityFixture::class, 'title');

        self::assertSame('string', $metadata->propertyTypeName($property));
        self::assertSame(ManageWave27StatusFixture::class, $metadata->enumTypeFor(ManageWave27EntityFixture::class, 'status'));
        self::assertInstanceOf(ORM\Column::class, $metadata->attributeInstance(
            new \ReflectionProperty(ManageWave27EntityFixture::class, 'status'),
            ORM\Column::class,
        ));
    }

    public function testReflectionInspectorRemainsBackwardCompatibleFacade(): void
    {
        $entity = new ManageWave27EntityFixture();
        $inspector = new ManageEntityReflectionInspector();

        self::assertTrue($inspector->writeField(ManageWave27EntityFixture::class, $entity, 'title', 'Facade'));
        self::assertSame('Facade', $inspector->readField($entity, 'title'));
        self::assertSame(ManageWave27StatusFixture::class, $inspector->enumTypeFor(ManageWave27EntityFixture::class, 'status'));
    }
}
