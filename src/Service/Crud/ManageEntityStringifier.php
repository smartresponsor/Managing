<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

final class ManageEntityStringifier
{
    public function stringify(mixed $value): string
    {
        if (null === $value) {
            return '';
        }

        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_array($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            return false === $encoded ? 'array' : $encoded;
        }

        return (string) get_debug_type($value);
    }
}
