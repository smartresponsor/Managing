<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

/**
 * Formats entity property names into deterministic EasyAdmin labels.
 */
final class ManageCrudFieldLabeler
{
    public function labelFor(string $field): string
    {
        $label = str_replace('_', ' ', $field);
        $label = (string) preg_replace('/(?<!^)[A-Z]/', ' $0', $label);
        $label = (string) preg_replace('/\s+/', ' ', $label);

        return ucfirst(trim($label));
    }
}
