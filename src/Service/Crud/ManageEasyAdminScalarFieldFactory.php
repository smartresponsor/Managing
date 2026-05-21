<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

/**
 * Owns scalar EasyAdmin field construction and Doctrine/PHP type mapping.
 */
final class ManageEasyAdminScalarFieldFactory
{
    public function __construct(
        private readonly ManageCrudFieldPolicy $fieldPolicy = new ManageCrudFieldPolicy(),
        private readonly ManageBackedEnumChoiceBuilder $enumChoiceBuilder = new ManageBackedEnumChoiceBuilder(),
    ) {
    }

    public function explicitField(string $fieldName, string $label, string $fieldType): object
    {
        return match ($fieldType) {
            'textarea' => TextareaField::new($fieldName, $label),
            'email' => EmailField::new($fieldName, $label),
            'url' => UrlField::new($fieldName, $label),
            'boolean' => BooleanField::new($fieldName, $label),
            'integer' => IntegerField::new($fieldName, $label),
            'number' => NumberField::new($fieldName, $label),
            'date' => DateField::new($fieldName, $label),
            'datetime' => DateTimeField::new($fieldName, $label),
            'time' => TimeField::new($fieldName, $label),
            default => TextField::new($fieldName, $label),
        };
    }

    public function enumField(string $fieldName, string $label, string $enumType): object
    {
        return ChoiceField::new($fieldName, $label)
            ->setChoices($this->enumChoiceBuilder->choicesFor($enumType));
    }

    public function fieldForPhpType(string $fieldName, string $label, ?string $propertyType): ?object
    {
        return match ($propertyType) {
            'bool' => BooleanField::new($fieldName, $label),
            'int' => IntegerField::new($fieldName, $label),
            'float' => NumberField::new($fieldName, $label),
            \DateTimeImmutable::class, \DateTimeInterface::class, \DateTime::class => DateTimeField::new($fieldName, $label),
            default => null,
        };
    }

    public function fieldForDoctrineType(string $fieldName, string $label, ?string $doctrineType, ?int $length): object
    {
        return match ($doctrineType ?? 'string') {
            'boolean' => BooleanField::new($fieldName, $label),
            'integer', 'smallint', 'bigint' => IntegerField::new($fieldName, $label),
            'float', 'decimal' => NumberField::new($fieldName, $label),
            'date', 'date_immutable' => DateField::new($fieldName, $label),
            'datetime', 'datetime_immutable' => DateTimeField::new($fieldName, $label),
            'time', 'time_immutable' => TimeField::new($fieldName, $label),
            'text' => TextareaField::new($fieldName, $label),
            'guid' => $this->guidField($fieldName, $label),
            default => $this->stringField($fieldName, $label, $length),
        };
    }

    public function stringField(string $fieldName, string $label, ?int $length = null): object
    {
        if ($this->fieldPolicy->looksLikeEmailField($fieldName)) {
            return EmailField::new($fieldName, $label);
        }

        if ($this->fieldPolicy->looksLikeUrlField($fieldName)) {
            return UrlField::new($fieldName, $label);
        }

        if ($this->fieldPolicy->looksLikeLongTextField($fieldName) || (null !== $length && $length > 255)) {
            return TextareaField::new($fieldName, $label);
        }

        return TextField::new($fieldName, $label);
    }

    private function guidField(string $fieldName, string $label): object
    {
        if ($this->fieldPolicy->looksLikeEmailField($fieldName)) {
            return EmailField::new($fieldName, $label);
        }

        if ($this->fieldPolicy->looksLikeUrlField($fieldName)) {
            return UrlField::new($fieldName, $label);
        }

        return TextField::new($fieldName, $label);
    }
}
