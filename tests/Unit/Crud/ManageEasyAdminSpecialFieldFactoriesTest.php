<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Service\Crud\ManageEasyAdminArrayChoiceFieldFactory;
use App\Managing\Service\Crud\ManageEasyAdminAssociationFieldFactory;
use Doctrine\ORM\Mapping as ORM;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use PHPUnit\Framework\TestCase;

final class ManageEasyAdminSpecialFieldFactoriesTest extends TestCase
{
    public function testBuildsAssociationFieldForDoctrineRelation(): void
    {
        $property = new \ReflectionProperty(ManageWave23RelationFixture::class, 'owner');
        $field = (new ManageEasyAdminAssociationFieldFactory())->fieldForProperty($property);

        self::assertInstanceOf(AssociationField::class, $field);
    }

    public function testIgnoresNonRelationProperty(): void
    {
        $property = new \ReflectionProperty(ManageWave23ArrayChoiceFixture::class, 'flags');

        self::assertNull((new ManageEasyAdminAssociationFieldFactory())->fieldForProperty($property));
    }

    public function testBuildsArrayChoiceFieldWhenChoicesAreConfigured(): void
    {
        $property = new \ReflectionProperty(ManageWave23ArrayChoiceFixture::class, 'flags');
        $field = (new ManageEasyAdminArrayChoiceFieldFactory())->fieldForProperty($property, [
            'flags' => ['Featured' => 'featured'],
        ]);

        self::assertInstanceOf(ChoiceField::class, $field);
    }

    public function testIgnoresArrayChoiceFieldWithoutConfiguredChoices(): void
    {
        $property = new \ReflectionProperty(ManageWave23ArrayChoiceFixture::class, 'flags');

        self::assertNull((new ManageEasyAdminArrayChoiceFieldFactory())->fieldForProperty($property, []));
    }
}

final class ManageWave23RelationFixture
{
    #[ORM\ManyToOne(targetEntity: ManageWave23RelatedFixture::class)]
    private ?ManageWave23RelatedFixture $owner = null;
}

final class ManageWave23RelatedFixture
{
}

final class ManageWave23ArrayChoiceFixture
{
    /** @var list<string> */
    #[ORM\Column(type: 'json')]
    private array $flags = [];
}
