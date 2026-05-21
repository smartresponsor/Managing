<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;

final class ManagePublicationStateHandler
{
    public function __construct(private readonly ManagePublicationFieldStateAccessor $fieldStateAccessor = new ManagePublicationFieldStateAccessor())
    {
    }

    /**
     * @param list<string> $flagCandidates
     * @param list<string> $dateCandidates
     */
    public function supportsPublication(string $entityFqcn, array $flagCandidates, array $dateCandidates): bool
    {
        return $this->fieldStateAccessor->supports($entityFqcn, $flagCandidates, $dateCandidates);
    }

    /**
     * @param list<string> $flagCandidates
     * @param list<string> $dateCandidates
     */
    public function isPublished(string $entityFqcn, object $entity, array $flagCandidates, array $dateCandidates): bool
    {
        return $this->fieldStateAccessor->isPublished($entityFqcn, $entity, $flagCandidates, $dateCandidates);
    }

    /**
     * @param list<string> $flagCandidates
     * @param list<string> $dateCandidates
     */
    public function canPublish(string $entityFqcn, object $entity, array $flagCandidates, array $dateCandidates): bool
    {
        if (!$this->supportsPublication($entityFqcn, $flagCandidates, $dateCandidates)) {
            return false;
        }

        return !$this->isPublished($entityFqcn, $entity, $flagCandidates, $dateCandidates);
    }

    /**
     * @param list<string> $flagCandidates
     * @param list<string> $dateCandidates
     */
    public function canUnpublish(string $entityFqcn, object $entity, array $flagCandidates, array $dateCandidates): bool
    {
        if (!$this->supportsPublication($entityFqcn, $flagCandidates, $dateCandidates)) {
            return false;
        }

        return $this->isPublished($entityFqcn, $entity, $flagCandidates, $dateCandidates);
    }

    /**
     * @param list<string> $flagCandidates
     * @param list<string> $dateCandidates
     */
    public function setPublicationState(string $entityFqcn, object $entity, bool $published, array $flagCandidates, array $dateCandidates): void
    {
        $this->fieldStateAccessor->writePublishedState($entityFqcn, $entity, $published, $flagCandidates, $dateCandidates);
    }

    /**
     * @param list<string> $flagCandidates
     * @param list<string> $dateCandidates
     */
    public function setBatchPublicationState(BatchActionDto $batchActionDto, EntityManagerInterface $entityManager, bool $published, array $flagCandidates, array $dateCandidates): void
    {
        $entityFqcn = $batchActionDto->getEntityFqcn();
        $repository = $entityManager->getRepository($entityFqcn);

        foreach ($batchActionDto->getEntityIds() as $entityId) {
            $entity = $repository->find($entityId);
            if (!is_object($entity)) {
                continue;
            }

            $this->setPublicationState($entityFqcn, $entity, $published, $flagCandidates, $dateCandidates);
        }

        $entityManager->flush();
    }
}
