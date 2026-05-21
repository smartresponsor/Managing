<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use Doctrine\ORM\Mapping as ORM;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

/**
 * Builds EasyAdmin association fields for Doctrine relation properties.
 *
 * Relation detection is kept outside the main field builder so association
 * handling can evolve independently from scalar, enum and array field logic.
 */
final class ManageEasyAdminAssociationFieldFactory
{
    public function __construct(
        private readonly ManageEntityReflectionInspector $inspector = new ManageEntityReflectionInspector(),
        private readonly ManageCrudFieldLabeler $labeler = new ManageCrudFieldLabeler(),
        private readonly ManageAssociationLabelResolver $associationLabelResolver = new ManageAssociationLabelResolver(),
    ) {
    }

    public function fieldForProperty(\ReflectionProperty $property): ?object
    {
        $fieldName = $property->getName();
        $label = $this->labeler->labelFor($fieldName);

        foreach ([ORM\ManyToOne::class, ORM\OneToOne::class] as $attributeClass) {
            $attribute = $this->inspector->attributeInstance($property, $attributeClass);
            if (null === $attribute) {
                continue;
            }

            return AssociationField::new($fieldName, $label)
                ->renderAsNativeWidget()
                ->setFormTypeOption('choice_label', fn (object $choice): string => $this->associationLabelResolver->label($choice));
        }

        return null;
    }
}
