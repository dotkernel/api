<?php

declare(strict_types=1);

namespace ApiTest\Unit\App\Attribute;

use Api\App\Attribute\MethodDeprecation;
use Api\App\Exception\DeprecationSunsetException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class MethodDeprecationTest extends TestCase
{
    public function testInvalidDateThrowsException(): void
    {
        $class = new class {
            #[MethodDeprecation(
                sunset: 'invalid-01-01',
                link: 'test-link',
                deprecationReason: 'test-deprecation-reason',
            )]
            public function test(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($class);
        $attributes      = $this->getAttributes($reflectionClass);

        $this->expectException(DeprecationSunsetException::class);

        $attributes[0]->newInstance();
    }

    public function testValidDatePassesValidation(): void
    {
        $class = new class {
            #[MethodDeprecation(
                sunset: '2038-01-01',
                link: 'test-link',
                deprecationReason: 'test-deprecation-reason',
            )]
            public function test(): void
            {
            }
        };

        $reflectionClass = new ReflectionClass($class);
        $attributes      = $this->getAttributes($reflectionClass);

        $attribute = $attributes[0]->newInstance();

        $this->assertSame('2038-01-01', $attribute->sunset);
        $this->assertSame('test-link', $attribute->link);
        $this->assertSame('test-deprecation-reason', $attribute->deprecationReason);
    }

    private function getAttributes(ReflectionClass $reflectionClass): array
    {
        $methods = $reflectionClass->getMethods();
        return $methods[0]->getAttributes(MethodDeprecation::class);
    }
}
