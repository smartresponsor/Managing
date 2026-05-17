<?php

declare(strict_types=1);

namespace App\Managing\Service\Admin;

use App\Managing\ServiceInterface\Admin\ManageAdminRegistryInterface;

final readonly class ManageComponentResolver
{
    public function __construct(
        private ManageAdminRegistryInterface $adminRegistry,
        private ManageComponentTokenParser $tokenParser,
    ) {
    }

    public function resolve(string $token): string
    {
        $token = $this->tokenParser->tail($token);
        if ('' === $token) {
            return '';
        }

        $componentKeys = $this->componentKeys();
        if (in_array($token, $componentKeys, true)) {
            return $token;
        }

        $candidates = array_values(array_filter(
            $componentKeys,
            static fn (string $componentKey): bool => str_starts_with($componentKey, $token),
        ));

        if (1 === count($candidates)) {
            return $candidates[0];
        }

        return '';
    }

    /**
     * @return list<string>
     */
    private function componentKeys(): array
    {
        $componentKeys = [];

        foreach ($this->adminRegistry->getCrudResources() as $resource) {
            $componentKeys[$resource->componentKey] = true;
        }

        $componentKeys = array_keys($componentKeys);
        sort($componentKeys);

        return array_values($componentKeys);
    }
}
