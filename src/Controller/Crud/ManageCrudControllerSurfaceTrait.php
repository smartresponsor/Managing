<?php

declare(strict_types=1);

namespace App\Managing\Controller\Crud;

/**
 * Entity-surface and publication guard helpers for Manage content controllers.
 *
 * The public EasyAdmin methods stay in the abstract controller; this trait keeps
 * the field/filter/action support calculations grouped as controller internals.
 */
trait ManageCrudControllerSurfaceTrait
{
    /** @return list<string> */
    private function searchFields(): array
    {
        return $this->entitySurfaceResolver()->searchFields(
            static::getEntityFqcn(),
            static::manageSearchFieldCandidates(),
        );
    }

    /** @return list<string> */
    private function statusFields(): array
    {
        return $this->entitySurfaceResolver()->statusFields(
            static::getEntityFqcn(),
            static::manageStatusFieldCandidates(),
        );
    }

    /** @return list<string> */
    private function publicationFlagFields(): array
    {
        return $this->entitySurfaceResolver()->publicationFlagFields(
            static::getEntityFqcn(),
            static::managePublicationFlagCandidates(),
        );
    }

    /** @return list<string> */
    private function filterDateFields(): array
    {
        return $this->entitySurfaceResolver()->filterDateFields(
            static::getEntityFqcn(),
            static::managePublicationDateCandidates(),
        );
    }

    /** @return list<string> */
    private function statusFieldCandidates(): array
    {
        return $this->entitySurfaceResolver()->statusFieldCandidates(static::manageStatusFieldCandidates());
    }

    /** @return list<string> */
    private function publicationFlagCandidates(): array
    {
        return $this->entitySurfaceResolver()->publicationFlagFieldCandidates(static::managePublicationFlagCandidates());
    }

    /** @return list<string> */
    private function publicationDateCandidates(): array
    {
        return $this->entitySurfaceResolver()->publicationDateFieldCandidates(static::managePublicationDateCandidates());
    }

    private function supportsPublication(): bool
    {
        return $this->publicationWorkflow()->supports(
            static::getEntityFqcn(),
            $this->publicationFlagCandidates(),
            $this->publicationDateCandidates(),
        );
    }

    private function canPublishEntity(object $entity): bool
    {
        return $this->publicationWorkflow()->canPublish(
            static::getEntityFqcn(),
            $entity,
            $this->publicationFlagCandidates(),
            $this->publicationDateCandidates(),
        );
    }

    private function canUnpublishEntity(object $entity): bool
    {
        return $this->publicationWorkflow()->canUnpublish(
            static::getEntityFqcn(),
            $entity,
            $this->publicationFlagCandidates(),
            $this->publicationDateCandidates(),
        );
    }
}
