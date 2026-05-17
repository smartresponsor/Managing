<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

final class ManageComponentTokenParser
{
    public function normalize(string $token): string
    {
        $token = trim($token);
        $token = trim($token, '/');
        $token = preg_replace('{/+}', '/', $token) ?? $token;

        return strtolower($token);
    }

    /**
     * @return list<string>
     */
    public function segments(string $token): array
    {
        $normalized = $this->normalize($token);
        if ('' === $normalized) {
            return [];
        }

        return array_values(array_filter(explode('/', $normalized)));
    }

    public function tail(string $token): string
    {
        $segments = $this->segments($token);

        return [] === $segments ? '' : (string) end($segments);
    }
}
