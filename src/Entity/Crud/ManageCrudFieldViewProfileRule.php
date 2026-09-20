<?php

declare(strict_types=1);

namespace App\Managing\Entity\Crud;

use App\Managing\Trait\Crud\ManageCrudFieldViewProfileRuleAccessorTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * System-storage row for one Managing field view profile page rule.
 *
 * This entity stores presentation preferences only. It must not be used to grant
 * field access or to override Rolling/Administering deny decisions.
 */
#[ORM\Entity]
#[ORM\Table(name: 'manage_crud_field_view_profile_rule')]
#[ORM\UniqueConstraint(name: 'uniq_manage_crud_field_view_profile_rule_scope', columns: ['subject_identifier', 'resource_key', 'page_name'])]
#[ORM\Index(name: 'idx_manage_crud_field_view_profile_subject', columns: ['subject_identifier'])]
#[ORM\Index(name: 'idx_manage_crud_field_view_profile_resource', columns: ['resource_key'])]
#[ORM\Index(name: 'idx_manage_crud_field_view_profile_page', columns: ['page_name'])]
final class ManageCrudFieldViewProfileRule
{
    use ManageCrudFieldViewProfileRuleAccessorTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    /** @phpstan-ignore property.unusedType */
    private ?int $id = null;

    #[ORM\Column(name: 'subject_identifier', type: 'string', length: 220)]
    private string $subjectIdentifier;

    #[ORM\Column(name: 'resource_key', type: 'string', length: 255)]
    private string $resourceKey = '*';

    #[ORM\Column(name: 'page_name', type: 'string', length: 80)]
    private string $pageName;

    /** @var list<string> */
    #[ORM\Column(name: 'visible_fields', type: Types::JSON)]
    private array $visibleFields = [];

    /** @var list<string> */
    #[ORM\Column(name: 'hidden_fields', type: Types::JSON)]
    private array $hiddenFields = [];

    #[ORM\Column(name: 'actor_identifier', type: 'string', length: 220, nullable: true)]
    private ?string $actorIdentifier = null;

    #[ORM\Column(name: 'reason', type: 'text', nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    /**
     * @param list<string> $visibleFields
     * @param list<string> $hiddenFields
     */
    public function __construct(
        string $subjectIdentifier = '',
        string $pageName = '',
        ?string $resourceClass = null,
        array $visibleFields = [],
        array $hiddenFields = [],
        ?string $actorIdentifier = null,
        ?string $reason = null,
        ?\DateTimeImmutable $createdAt = null,
    ) {
        $this->subjectIdentifier = trim($subjectIdentifier);
        $this->pageName = trim($pageName);
        $this->resourceKey = self::resourceKeyFromClass($resourceClass);
        $this->visibleFields = self::normalizeFieldList($visibleFields);
        $this->hiddenFields = self::normalizeFieldList($hiddenFields);
        $this->actorIdentifier = self::nullableString($actorIdentifier);
        $this->reason = self::nullableString($reason);
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    /**
     * @param list<string> $visibleFields
     * @param list<string> $hiddenFields
     */
    public function replaceRule(array $visibleFields, array $hiddenFields, ?string $actorIdentifier = null, ?string $reason = null): self
    {
        $this->visibleFields = self::normalizeFieldList($visibleFields);
        $this->hiddenFields = self::normalizeFieldList($hiddenFields);
        $this->actorIdentifier = self::nullableString($actorIdentifier);
        $this->reason = self::nullableString($reason);
        $this->updatedAt = new \DateTimeImmutable();

        return $this;
    }

    public static function resourceKeyFromClass(?string $resourceClass): string
    {
        if (null === $resourceClass || '' === trim($resourceClass)) {
            return '*';
        }

        return trim($resourceClass);
    }

    /**
     * @param list<string> $values
     *
     * @return list<string>
     */
    private static function normalizeFieldList(array $values): array
    {
        $normalized = [];
        foreach ($values as $value) {
            $value = trim($value);
            if ('' !== $value && !in_array($value, $normalized, true)) {
                $normalized[] = $value;
            }
        }

        return $normalized;
    }

    private static function nullableString(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = trim($value);

        return '' === $value ? null : $value;
    }
}
