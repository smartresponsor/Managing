<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

final class ManageAssociationLabelResolver
{
    /** @param list<string> $labelFields */
    public function __construct(
        private readonly ManageEntityReflectionInspector $inspector = new ManageEntityReflectionInspector(),
        private readonly ManageEntityStringifier $stringifier = new ManageEntityStringifier(),
        private readonly array $labelFields = ['firstTitle', 'title', 'name', 'label', 'code', 'slug', 'number', 'reference', 'identifier', 'email', 'username'],
    ) {
    }

    public function label(object $choice): string
    {
        if (method_exists($choice, '__toString')) {
            try {
                $label = (string) $choice;
                if ('' !== trim($label)) {
                    return $label;
                }
            } catch (\Throwable) {
                // Fall through to reflective label guessing.
            }
        }

        foreach ($this->labelFields as $field) {
            $value = $this->inspector->readField($choice, $field);
            if (null === $value) {
                continue;
            }

            $label = $this->stringifier->stringify($value);
            if ('' !== trim($label)) {
                return $label;
            }
        }

        if (method_exists($choice, 'getId')) {
            $value = $choice->getId();
            if (null !== $value) {
                return $this->stringifier->stringify($value);
            }
        }

        if (method_exists($choice, 'id')) {
            $value = $choice->id();
            if (null !== $value) {
                return $this->stringifier->stringify($value);
            }
        }

        return get_debug_type($choice);
    }
}
