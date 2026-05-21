<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

final class ManageEntityInstantiator
{
    public function __construct(
        private readonly ?ManageConstructorArgumentResolver $argumentResolver = null,
    ) {
    }

    public function instantiate(string $entityFqcn): object
    {
        return $this->instantiateEntityForNewForm($entityFqcn);
    }

    /** @param list<class-string> $stack */
    private function instantiateEntityForNewForm(string $entityFqcn, array $stack = []): object
    {
        try {
            $reflectionClass = new \ReflectionClass($entityFqcn);
        } catch (\ReflectionException) {
            return new $entityFqcn();
        }

        if (!$reflectionClass->isInstantiable()) {
            return $reflectionClass->newInstanceWithoutConstructor();
        }

        $constructor = $reflectionClass->getConstructor();
        if (null === $constructor || [] === $constructor->getParameters()) {
            return $reflectionClass->newInstance();
        }

        if (in_array($entityFqcn, $stack, true)) {
            return $reflectionClass->newInstanceWithoutConstructor();
        }

        $stack[] = $entityFqcn;
        $arguments = [];
        foreach ($constructor->getParameters() as $parameter) {
            $arguments[] = $this->argumentResolver()->resolve(
                $parameter,
                $stack,
                fn (string $typeName, array $nestedStack): mixed => $this->instantiateConstructorObject($typeName, $nestedStack),
            );
        }

        try {
            return $reflectionClass->newInstanceArgs($arguments);
        } catch (\Throwable) {
            return $reflectionClass->newInstanceWithoutConstructor();
        }
    }

    /** @param list<class-string> $stack */
    private function instantiateConstructorObject(string $typeName, array $stack): mixed
    {
        try {
            return $this->instantiateEntityForNewForm($typeName, $stack);
        } catch (\Throwable) {
            $reflectionClass = new \ReflectionClass($typeName);

            return $reflectionClass->isInstantiable() ? $reflectionClass->newInstanceWithoutConstructor() : null;
        }
    }

    private function argumentResolver(): ManageConstructorArgumentResolver
    {
        return $this->argumentResolver ?? new ManageConstructorArgumentResolver();
    }
}
