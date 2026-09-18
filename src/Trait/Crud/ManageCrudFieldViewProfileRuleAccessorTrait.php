<?php

declare(strict_types=1);

namespace App\Managing\Trait\Crud;

trait ManageCrudFieldViewProfileRuleAccessorTrait
{
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
}
