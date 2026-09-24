<?php

declare(strict_types=1);

namespace App\Managing\Workflow\Crud;

use App\Managing\Handler\Crud\ManagePublicationStateHandler;
use App\Managing\RepositoryInterface\Crud\ManagePublicationRepositoryInterface;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;

/**
 * Orchestrates EasyAdmin publication actions for Manage CRUD controllers.
 *
 * The lower-level ManagePublicationStateHandler owns field mutation rules;
 * this workflow owns EasyAdmin action context handling so controllers can stay
 * focused on wiring the screen instead of duplicating publication command flow.
 */
final class ManageCrudPublicationWorkflow
{
    public function __construct(private readonly ManagePublicationStateHandler $stateHandler = new ManagePublicationStateHandler())
    {
    }

    /**
     * @param list<string> $flagCandidates
     * @param list<string> $dateCandidates
     */
    public function supports(string $entityFqcn, array $flagCandidates, array $dateCandidates): bool
    {
        return $this->stateHandler->supportsPublication($entityFqcn, $flagCandidates, $dateCandidates);
    }

    /**
     * @param list<string> $flagCandidates
     * @param list<string> $dateCandidates
     */
    public function canPublish(string $entityFqcn, object $entity, array $flagCandidates, array $dateCandidates): bool
    {
        return $this->stateHandler->canPublish($entityFqcn, $entity, $flagCandidates, $dateCandidates);
    }

    /**
     * @param list<string> $flagCandidates
     * @param list<string> $dateCandidates
     */
    public function canUnpublish(string $entityFqcn, object $entity, array $flagCandidates, array $dateCandidates): bool
    {
        return $this->stateHandler->canUnpublish($entityFqcn, $entity, $flagCandidates, $dateCandidates);
    }

    /**
     * @template TEntity of object
     *
     * @param AdminContext<TEntity> $context
     * @param list<string>          $flagCandidates
     * @param list<string>          $dateCandidates
     */
    public function setCurrentEntityPublicationState(
        AdminContext $context,
        ManagePublicationRepositoryInterface $publicationRepository,
        string $entityFqcn,
        bool $published,
        array $flagCandidates,
        array $dateCandidates,
    ): void {
        $entity = $context->getEntity()->getInstance();
        if (!is_object($entity)) {
            return;
        }

        $this->stateHandler->setPublicationState($entityFqcn, $entity, $published, $flagCandidates, $dateCandidates);
        $publicationRepository->flush($entityFqcn);
    }

    /**
     * @template TEntity of object
     *
     * @param BatchActionDto<TEntity> $batchActionDto
     * @param list<string>            $flagCandidates
     * @param list<string>            $dateCandidates
     */
    public function setBatchPublicationState(
        BatchActionDto $batchActionDto,
        ManagePublicationRepositoryInterface $publicationRepository,
        bool $published,
        array $flagCandidates,
        array $dateCandidates,
    ): void {
        $this->stateHandler->setBatchPublicationState($batchActionDto, $publicationRepository, $published, $flagCandidates, $dateCandidates);
    }
}
