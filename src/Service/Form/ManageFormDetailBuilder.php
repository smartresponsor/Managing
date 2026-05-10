<?php

declare(strict_types=1);

namespace App\Managing\Service\Form;

use App\Managing\ServiceInterface\Crud\ManageCrudActionUrlBuilderInterface;
use App\Managing\ServiceInterface\Crud\ManageCrudResourceRegistryInterface;
use App\Managing\ServiceInterface\Form\ManageFormDetailBuilderInterface;
use App\Managing\ServiceInterface\Form\ManageFormRegistryInterface;
use App\Managing\Value\ManageCrudResourceDefinition;
use App\Managing\Value\ManageFormDefinition;

final readonly class ManageFormDetailBuilder implements ManageFormDetailBuilderInterface
{
    public function __construct(
        private ManageFormRegistryInterface $formRegistry,
        private ManageCrudResourceRegistryInterface $crudResourceRegistry,
        private ManageCrudActionUrlBuilderInterface $actionUrlBuilder,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function buildFormDetail(string $componentKey, string $formKey): ?array
    {
        $form = $this->findForm($componentKey, $formKey);

        if (!$form instanceof ManageFormDefinition) {
            return null;
        }

        $resource = null !== $form->resourceKey ? $this->findResource($componentKey, $form->resourceKey) : null;

        return [
            'form' => $form,
            'resource' => $resource,
            'resourceActions' => $resource instanceof ManageCrudResourceDefinition ? $this->actionUrlBuilder->buildActionUrls($resource) : [],
            'formTypeExists' => '' !== $form->formTypeClass && class_exists($form->formTypeClass),
        ];
    }

    private function findForm(string $componentKey, string $formKey): ?ManageFormDefinition
    {
        foreach ($this->formRegistry->getForms() as $form) {
            if ($form->componentKey === $componentKey && $form->formKey === $formKey) {
                return $form;
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
