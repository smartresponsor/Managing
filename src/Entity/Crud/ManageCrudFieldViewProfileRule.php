<?php

declare(strict_types=1);

namespace App\Managing\Entity\Crud;

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
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
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

    public function id(): ?int
    {
        return $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function subjectIdentifier(): string
    {
        return $this->subjectIdentifier;
    }

    public function getSubjectIdentifier(): string
    {
        return $this->subjectIdentifier;
    }

    public function resourceKey(): string
    {
        return $this->resourceKey;
    }

    public function getResourceKey(): string
    {
        return $this->resourceKey;
    }

    public function pageName(): string
    {
        return $this->pageName;
    }

    public function getPageName(): string
    {
        return $this->pageName;
    }

    /** @return list<string> */
    public function visibleFields(): array
    {
        return $this->visibleFields;
    }

    /** @return list<string> */
    public function getVisibleFields(): array
    {
        return $this->visibleFields;
    }

    /** @return list<string> */
    public function hiddenFields(): array
    {
        return $this->hiddenFields;
    }

    /** @return list<string> */
    public function getHiddenFields(): array
    {
        return $this->hiddenFields;
    }

    public function actorIdentifier(): ?string
    {
        return $this->actorIdentifier;
    }

    public function getActorIdentifier(): ?string
    {
        return $this->actorIdentifier;
    }

    public function reason(): ?string
    {
        return $this->reason;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function resourceClass(): ?string
    {
        return '*' === $this->resourceKey ? null : $this->resourceKey;
    }

    public function targetsResource(): bool
    {
        return '*' !== $this->resourceKey;
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

    /** @param list<string> $values @return list<string> */
    private static function normalizeFieldList(array $values): array
    {
        $normalized = [];
        foreach ($values as $value) {
            if (!is_string($value)) {
                continue;
            }

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
