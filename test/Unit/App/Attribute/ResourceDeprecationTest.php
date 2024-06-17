<?php

declare(strict_types=1);

namespace ApiTest\Unit\App\Attribute;

use Api\App\Attribute\ResourceDeprecation;
use Api\App\Exception\DeprecationSunsetException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ResourceDeprecationTest extends TestCase
{
    public function testInvalidDateThrowsException(): void
    {
        $class = new #[ResourceDeprecation(
            sunset: 'invalid-01-01',
            link: 'test-link',
            deprecationReason: 'test-deprecation-reason',
        )] class {
        };

        $reflectionClass = new ReflectionClass($class);
        $attributes      = $reflectionClass->getAttributes(ResourceDeprecation::class);

        $this->expectException(DeprecationSunsetException::class);

        $attributes[0]->newInstance();
    }

    public function testValidDatePassesValidation(): void
    {
        $class = new #[ResourceDeprecation(
            sunset: '2038-01-01',
            link: 'test-link',
            deprecationReason: 'test-deprecation-reason',
        )] class {
        };

        $reflectionClass = new ReflectionClass($class);
        $attributes      = $reflectionClass->getAttributes(ResourceDeprecation::class);

        $attribute = $attributes[0]->newInstance();

        $this->assertSame('2038-01-01', $attribute->sunset);
        $this->assertSame('test-link', $attribute->link);
        $this->assertSame('test-deprecation-reason', $attribute->deprecationReason);
    }
}
