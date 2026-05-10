<?php

declare(strict_types=1);

namespace App\Managing\Service\Relation;

use App\Managing\ServiceInterface\Crud\ManageCrudResourceRegistryInterface;
use App\Managing\ServiceInterface\Relation\ManageRelationDetailBuilderInterface;
use App\Managing\ServiceInterface\Relation\ManageRelationRegistryInterface;
use App\Managing\Value\ManageCrudResourceDefinition;
use App\Managing\Value\ManageRelationDefinition;

final readonly class ManageRelationDetailBuilder implements ManageRelationDetailBuilderInterface
{
    public function __construct(
        private ManageRelationRegistryInterface $relationRegistry,
        private ManageCrudResourceRegistryInterface $crudResourceRegistry,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buildRelationDetail(string $componentKey, string $relationKey): ?array
    {
        $relation = $this->findRelation($componentKey, $relationKey);

        if (!$relation instanceof ManageRelationDefinition) {
            return null;
        }

        return [
            'relation' => $relation,
            'sourceResource' => $this->findResource($relation->componentKey, $relation->sourceResourceKey),
            'targetResource' => $this->findResource($relation->componentKey, $relation->targetResourceKey),
        ];
    }

    private function findRelation(string $componentKey, string $relationKey): ?ManageRelationDefinition
    {
        foreach ($this->relationRegistry->getRelations() as $relation) {
            if ($relation->componentKey === $componentKey && $relation->relationKey === $relationKey) {
                return $relation;
            }
        }

        return null;
    }

    private function findResource(string $componentKey, string $resourceKey): ?ManageCrudResourceDefinition
    {
        foreach ($this->crudResourceRegistry->getCrudResources() as $resource) {
            if ($resource->componentKey === $componentKey && $resource->resourceKey === $resourceKey) {
                return $resource;
            }
        }

        return null;
    }
}
