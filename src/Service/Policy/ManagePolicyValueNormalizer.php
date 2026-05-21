<?php

declare(strict_types=1);

namespace App\Managing\Service\Policy;

/**
 * Normalizes configuration values consumed by Managing policy services.
 *
 * Keeping map/list coercion here prevents each policy from carrying its own
 * low-level defensive parsing logic.
 */
final class ManagePolicyValueNormalizer
{
    /**
     * @return list<string>
     */
    public function stringList(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }

        $result = [];
        foreach ($values as $value) {
            if (!is_string($value)) {
                continue;
            }

            $value = trim($value);
            if ('' !== $value) {
                $result[] = $value;
            }
        }

        return array_values(array_unique($result));
    }

    /**
     * @return list<string>
     */
    public function lowercaseStringList(mixed $values): array
    {
        $result = [];
        foreach ($this->stringList($values) as $value) {
            $value = strtolower($value);
            if ('' !== $value) {
                $result[] = $value;
            }
        }

        return array_values(array_unique($result));
    }

    /**
     * @return array<string, string>
     */
    public function normalizedStringMap(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }

        $result = [];
        foreach ($values as $key => $value) {
            if (!is_string($key) || !is_string($value)) {
                continue;
            }

            $key = strtolower(trim($key));
            $value = strtolower(trim($value));
            if ('' !== $key && '' !== $value) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @return array<string, int>
     */
    public function intMap(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }

        $result = [];
        foreach ($values as $key => $value) {
            if (!is_string($key) || '' === trim($key) || (!is_int($value) && !is_numeric($value))) {
                continue;
            }

            $result[trim($key)] = (int) $value;
        }

        return $result;
    }
}
