<?php

declare(strict_types=1);

namespace App\Managing\Tests\Unit\Crud;

use App\Managing\Service\Crud\ManageConstructorArgumentResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ManageConstructorArgumentResolverTest extends TestCase
{
    #[Test]
    public function testResolvesSafeBuiltInPlaceholders(): void
    {
        $resolver = new ManageConstructorArgumentResolver();
        $parameters = $this->parameters(ConstructorArgumentResolverProbe::class);

        self::assertSame('', $resolver->resolve($parameters['name'], [], self::objectResolver(...)));
        self::assertSame(0, $resolver->resolve($parameters['count'], [], self::objectResolver(...)));
        self::assertFalse($resolver->resolve($parameters['enabled'], [], self::objectResolver(...)));
        self::assertSame([], $resolver->resolve($parameters['items'], [], self::objectResolver(...)));
        self::assertSame('fixed', $resolver->resolve($parameters['defaulted'], [], self::objectResolver(...)));
    }

    #[Test]
    public function testResolvesDatesEnumsObjectsAndNullableTypes(): void
    {
        $resolver = new ManageConstructorArgumentResolver();
        $parameters = $this->parameters(ConstructorArgumentResolverObjectProbe::class);

        self::assertInstanceOf(\DateTimeImmutable::class, $resolver->resolve($parameters['createdAt'], [], self::objectResolver(...)));
        self::assertSame(ConstructorArgumentResolverStatus::Draft, $resolver->resolve($parameters['status'], [], self::objectResolver(...)));
        self::assertInstanceOf(ConstructorArgumentResolverNestedProbe::class, $resolver->resolve($parameters['nested'], [], self::objectResolver(...)));
        self::assertNull($resolver->resolve($parameters['nullable'], [], self::objectResolver(...)));
    }

    /** @return array<string, \ReflectionParameter> */
    private function parameters(string $className): array
    {
        $constructor = (new \ReflectionClass($className))->getConstructor();
        self::assertNotNull($constructor);

        $parameters = [];
        foreach ($constructor->getParameters() as $parameter) {
            $parameters[$parameter->getName()] = $parameter;
        }

        return $parameters;
    }

    /** @param list<class-string> $stack */
    private static function objectResolver(string $typeName, array $stack): object
    {
        return new $typeName();
    }
}

final class ConstructorArgumentResolverProbe
{
    /** @param list<string> $items */
    public function __construct(
        public string $name,
        public int $count,
        public bool $enabled,
        public array $items,
        public string $defaulted = 'fixed',
    ) {
    }
}

final class ConstructorArgumentResolverObjectProbe
{
    public function __construct(
        public \DateTimeImmutable $createdAt,
        public ConstructorArgumentResolverStatus $status,
        public ConstructorArgumentResolverNestedProbe $nested,
        public ?string $nullable,
    ) {
    }
}

final class ConstructorArgumentResolverNestedProbe
{
}

enum ConstructorArgumentResolverStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
}
