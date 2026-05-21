<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

/**
 * Builds deterministic EasyAdmin choice maps for backed enums.
 */
final class ManageBackedEnumChoiceBuilder
{
    /** @return array<string, \BackedEnum> */
    public function choicesFor(string $enumType): array
    {
        if (!enum_exists($enumType) || !is_subclass_of($enumType, \BackedEnum::class)) {
            return [];
        }

        $choices = [];
        foreach ($enumType::cases() as $case) {
            $choices[$this->labelForValue((string) $case->value)] = $case;
        }

        return $choices;
    }

    private function labelForValue(string $value): string
    {
        return ucfirst(str_replace('_', ' ', $value));
    }
}
