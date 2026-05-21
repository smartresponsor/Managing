<?php

declare(strict_types=1);

namespace App\Managing\Service\Crud;

/**
 * Resolves safe placeholder constructor arguments for admin create forms.
 *
 * This service is intentionally conservative: it prefers defaults/nulls and
 * simple scalar placeholders so EasyAdmin can render new forms for constructor-
 * heavy host entities without coupling Managing to those host domains.
 */
final class ManageConstructorArgumentResolver
{
    /**
     * @param list<class-string>                                $stack
     * @param callable(class-string, list<class-string>): mixed $objectResolver
     */
    public function resolve(\ReflectionParameter $parameter, array $stack, callable $objectResolver): mixed
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        $type = $parameter->getType();
        if ($type instanceof \ReflectionUnionType) {
            return $this->resolveUnion($type, $stack, $objectResolver);
        }

        if ($type instanceof \ReflectionNamedType) {
            return $this->resolveNamed($type, $stack, $objectResolver);
        }

        return null;
    }

    /**
     * @param list<class-string>                                $stack
     * @param callable(class-string, list<class-string>): mixed $objectResolver
     */
    private function resolveUnion(\ReflectionUnionType $type, array $stack, callable $objectResolver): mixed
    {
        foreach ($type->getTypes() as $namedType) {
            $value = $this->resolveNamed($namedType, $stack, $objectResolver);
            if (null !== $value || $namedType->allowsNull()) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param list<class-string>                                $stack
     * @param callable(class-string, list<class-string>): mixed $objectResolver
     */
    private function resolveNamed(\ReflectionNamedType $type, array $stack, callable $objectResolver): mixed
    {
        if ($type->allowsNull()) {
            return null;
        }

        if ($type->isBuiltin()) {
            return $this->resolveBuiltin($type->getName());
        }

        $typeName = $type->getName();
        if (\DateTimeImmutable::class === $typeName || \DateTimeInterface::class === $typeName || \DateTime::class === $typeName) {
            return new \DateTimeImmutable();
        }

        if (enum_exists($typeName)) {
            $cases = $typeName::cases();

            return $cases[0] ?? null;
        }

        if (!class_exists($typeName)) {
            return null;
        }

        /* @var class-string $typeName */
        return $objectResolver($typeName, $stack);
    }

    private function resolveBuiltin(string $typeName): mixed
    {
        return match ($typeName) {
            'string' => '',
            'int' => 0,
            'float' => 0.0,
            'bool' => false,
            'array' => [],
            'callable' => static fn (): null => null,
            'iterable' => [],
            'mixed' => null,
            default => null,
        };
    }
}
