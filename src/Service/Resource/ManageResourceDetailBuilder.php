<?php

declare(strict_types=1);

namespace App\Managing\Service\Resource;

use App\Managing\ServiceInterface\Crud\ManageCrudActionUrlBuilderInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudResourceRegistryInterface;
use App\Managing\ServiceInterface\Form\ManageFormRegistryInterface;
use App\Managing\ServiceInterface\Relation\ManageRelationRegistryInterface;
use App\Managing\ServiceInterface\Resource\ManageResourceDetailBuilderInterface;
use App\Managing\Value\ManageCrudResourceDefinition;

final readonly class ManageResourceDetailBuilder implements ManageResourceDetailBuilderInterface
{
    public function __construct(
        private ManageCrudResourceRegistryInterface $crudResourceRegistry,
        private ManageFormRegistryInterface $formRegistry,
        private ManageRelationRegistryInterface $relationRegistry,
        private ManageCrudActionUrlBuilderInterface $actionUrlBuilder,
    ) {
    }

    public function buildResourceDetail(string $componentKey, string $resourceKey): ?array
    {
        $resource = $this->findResource($componentKey, $resourceKey);

        if (!$resource instanceof ManageCrudResourceDefinition) {
            return null;
        }

        return [
            'resource' => $resource,
            'actions' => $this->actionUrlBuilder->buildActionUrls($resource),
            'forms' => $this->findForms($componentKey, $resourceKey),
            'outgoingRelations' => $this->findOutgoingRelations($componentKey, $resourceKey),
            'incomingRelations' => $this->findIncomingRelations($componentKey, $resourceKey),
        ];
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

    /** @return list<object> */
    private function findForms(string $componentKey, string $resourceKey): array
    {
        $forms = [];

        foreach ($this->formRegistry->getForms() as $form) {
            if ($form->componentKey === $componentKey && $form->resourceKey === $resourceKey) {
                $forms[] = $form;
            }
        }

        return $forms;
    }

    /** @return list<object> */
    private function findOutgoingRelations(string $componentKey, string $resourceKey): array
    {
        $relations = [];

        foreach ($this->relationRegistry->getRelations() as $relation) {
            if ($relation->componentKey === $componentKey && $relation->sourceResourceKey === $resourceKey) {
                $relations[] = $relation;
            }
        }

        return $relations;
    }

    /** @return list<object> */
    private function findIncomingRelations(string $componentKey, string $resourceKey): array
    {
        $relations = [];

        foreach ($this->relationRegistry->getRelations() as $relation) {
            if ($relation->componentKey === $componentKey && $relation->targetResourceKey === $resourceKey) {
                $relations[] = $relation;
            }
        }

        return $relations;
    }
}
