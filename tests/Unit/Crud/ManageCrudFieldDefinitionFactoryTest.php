<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Factory\Crud\ManageCrudFieldDefinitionFactory;
use App\Managing\Policy\Crud\ManageCrudFieldPolicy;
use App\Managing\Value\Crud\ManageCrudFieldAccessContext;
use Doctrine\ORM\Mapping as ORM;
use PHPUnit\Framework\TestCase;

final class ManageCrudFieldDefinitionFactoryTest extends TestCase
{
    public function testDefinitionsUseConfiguredVocabularyBeforeEasyAdminFieldsAreBuilt(): void
    {
        $factory = $this->factory();
        $indexDefinitions = $factory->definitions(
            FieldDefinitionFactoryFixtureEntity::class,
            ManageCrudFieldAccessContext::PAGE_INDEX,
            statusCandidates: ['state'],
            publicationFlagCandidates: ['enabled'],
            publicationDateCandidates: ['publishedAt'],
        );

        self::assertSame([
            'uuid',
            'headline',
            'sku',
            'state',
            'enabled',
            'publishedAt',
            'recordedAt',
        ], array_map(static fn ($definition): string => $definition->fieldName, $indexDefinitions));
        self::assertFalse($indexDefinitions[0]->hideable);
        self::assertSame('identifier', $indexDefinitions[0]->fieldType);
    }

    public function testFormDefinitionsExcludeConfiguredSurfaceFields(): void
    {
        $formDefinitions = $this->factory()->definitions(
            FieldDefinitionFactoryFixtureEntity::class,
            ManageCrudFieldAccessContext::PAGE_NEW,
            statusCandidates: ['state'],
            publicationFlagCandidates: ['enabled'],
            publicationDateCandidates: ['publishedAt'],
            fieldTypeOverrides: ['externalLink' => 'url'],
        );

        self::assertSame([
            'headline',
            'sku',
            'state',
            'enabled',
            'summary',
            'contactMail',
            'externalLink',
            'privateNotes',
        ], array_map(static fn ($definition): string => $definition->fieldName, $formDefinitions));
        self::assertSame('email', $formDefinitions[5]->fieldType);
        self::assertSame('url', $formDefinitions[6]->fieldType);
        self::assertSame('textarea', $formDefinitions[7]->fieldType);
    }

    private function factory(): ManageCrudFieldDefinitionFactory
    {
        return new ManageCrudFieldDefinitionFactory(
            fieldPolicy: new ManageCrudFieldPolicy(
                primaryIdentifierFields: ['uuid'],
                titleFields: ['headline'],
                identityFields: ['sku'],
                descriptionFields: ['summary'],
                auditDateFields: ['recordedAt'],
                emailKeywords: ['mail'],
                longTextKeywords: ['notes'],
            ),
        );
    }
}

final class FieldDefinitionFactoryFixtureEntity
{
    #[ORM\Column(type: 'string')]
    private string $uuid = '';

    #[ORM\Column(type: 'string')]
    private string $headline = '';

    #[ORM\Column(type: 'string')]
    private string $sku = '';

    #[ORM\Column(type: 'string')]
    private string $state = '';

    #[ORM\Column(type: 'boolean')]
    private bool $enabled = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: 'text')]
    private string $summary = '';

    #[ORM\Column(type: 'string')]
    private string $contactMail = '';

    #[ORM\Column(type: 'string')]
    private string $externalLink = '';

    #[ORM\Column(type: 'text')]
    private string $privateNotes = '';

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $recordedAt = null;
}
