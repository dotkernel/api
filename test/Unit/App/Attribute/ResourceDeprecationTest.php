<?php

declare(strict_types=1);

namespace ApiTest\Unit\App\Attribute;

use Api\App\Attribute\ResourceDeprecation;
use Api\App\Exception\DeprecationSunsetException;
use Api\App\Middleware\DeprecationMiddleware;
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

    public function testToArray(): void
    {
        $class = new #[ResourceDeprecation(
            sunset: '2038-01-01',
            link: 'test-link',
            deprecationReason: 'test-deprecation-reason',
            rel: 'test-rel',
            type: 'test-type',
        )] class {
        };

        $reflectionClass = new ReflectionClass($class);
        $attributes      = $reflectionClass->getAttributes(ResourceDeprecation::class);

        $this->assertNotEmpty($attributes);
        $attribute = $attributes[0]->newInstance();

        $array = $attribute->toArray();

        $this->assertNotEmpty($array);
        $this->assertArrayHasKey('sunset', $array);
        $this->assertArrayHasKey('link', $array);
        $this->assertArrayHasKey('rel', $array);
        $this->assertArrayHasKey('type', $array);
        $this->assertArrayHasKey('deprecationReason', $array);
        $this->assertArrayHasKey('deprecationType', $array);

        $this->assertSame('2038-01-01', $array['sunset']);
        $this->assertSame('test-rel', $array['rel']);
        $this->assertSame('test-type', $array['type']);
        $this->assertSame('test-link', $array['link']);
        $this->assertSame('test-deprecation-reason', $array['deprecationReason']);
        $this->assertSame(DeprecationMiddleware::RESOURCE_DEPRECATION_ATTRIBUTE, $array['deprecationType']);
    }
}
