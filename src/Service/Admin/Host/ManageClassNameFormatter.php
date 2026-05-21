<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin\Host;

final class ManageClassNameFormatter
{
    public function shortClassName(string $className): string
    {
        $position = strrpos($className, '\\');

        return false === $position ? $className : substr($className, $position + 1);
    }

    public function studly(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9]+/', ' ', $value) ?? $value;
        $value = ucwords(strtolower(trim($value)));

        return str_replace(' ', '', $value);
    }

    public function slug(string $value): string
    {
        $value = preg_replace('/(?<!^)[A-Z]/', '_$0', $value) ?? $value;
        $value = strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '_', $value));

        return trim($value, '_') ?: 'app';
    }

    public function humanize(string $value): string
    {
        $value = str_replace(['_', '-'], ' ', $value);
        $value = preg_replace('/(?<!^)[A-Z]/', ' $0', $value) ?? $value;

        return ucfirst(strtolower(trim($value))) ?: 'Host application';
    }

    public function normalizeResourceShortName(string $shortName): string
    {
        $shortName = preg_replace('/(?:Entity|Record|Projection|Model|Dto|View|ReadModel|WriteModel)$/', '', $shortName) ?? $shortName;

        return strtolower($shortName);
    }
}
