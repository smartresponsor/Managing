<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

/**
 * Resolves and mutates publication fields on arbitrary host entities.
 *
 * The handler keeps batch/entity-manager orchestration; this accessor owns
 * the reflection-backed flag/date publication state contract.
 */
final class ManagePublicationFieldStateAccessor
{
    public function __construct(private readonly ManageEntityReflectionInspector $inspector = new ManageEntityReflectionInspector())
    {
    }

    /**
     * @param list<string> $flagCandidates
     * @param list<string> $dateCandidates
     */
    public function supports(string $entityFqcn, array $flagCandidates, array $dateCandidates): bool
    {
        return [] !== $this->inspector->existingFields($entityFqcn, $flagCandidates)
            || [] !== $this->inspector->existingFields($entityFqcn, $dateCandidates);
    }

    /**
     * @param list<string> $flagCandidates
     * @param list<string> $dateCandidates
     */
    public function isPublished(string $entityFqcn, object $entity, array $flagCandidates, array $dateCandidates): bool
    {
        foreach ($flagCandidates as $field) {
            if (!$this->inspector->hasField($entityFqcn, $field)) {
                continue;
            }

            $value = $this->inspector->readField($entity, $field);
            if (is_bool($value)) {
                return $value;
            }
        }

        foreach ($dateCandidates as $field) {
            if (!$this->inspector->hasField($entityFqcn, $field)) {
                continue;
            }

            return null !== $this->inspector->readField($entity, $field);
        }

        return false;
    }

    /**
     * @param list<string> $flagCandidates
     * @param list<string> $dateCandidates
     */
    public function writePublishedState(string $entityFqcn, object $entity, bool $published, array $flagCandidates, array $dateCandidates): void
    {
        foreach ($flagCandidates as $field) {
            if ($this->inspector->writeField($entityFqcn, $entity, $field, $published)) {
                break;
            }
        }

        foreach ($dateCandidates as $field) {
            if (!$this->inspector->hasField($entityFqcn, $field)) {
                continue;
            }

            $this->inspector->writeField($entityFqcn, $entity, $field, $published ? new \DateTimeImmutable() : null);
            break;
        }
    }
}
