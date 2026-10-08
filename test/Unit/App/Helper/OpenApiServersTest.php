<?php

declare(strict_types=1);

namespace ApiTest\Unit\App\Helper;

use Api\App\Helper\OpenApiServers;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class OpenApiServersTest extends TestCase
{
    private const string BASE_URL = 'https://ipa.example.com';

    public function testWithoutAdditionalServersOnlyTheProjectUrlIsListed(): void
    {
        $this->assertSame(
            [['url' => self::BASE_URL, 'description' => 'Production']],
            OpenApiServers::fromConfig(self::BASE_URL, 'Production', []),
        );
    }

    public function testTheProjectUrlMayHaveNoDescription(): void
    {
        $this->assertSame(
            [['url' => self::BASE_URL, 'description' => null]],
            OpenApiServers::fromConfig(self::BASE_URL, null, []),
        );
    }

    public function testAdditionalServersFollowTheProjectUrlInTheirConfiguredOrder(): void
    {
        $servers = OpenApiServers::fromConfig(self::BASE_URL, 'Production', [
            ['url' => 'https://sandbox.example.com', 'description' => 'Sandbox'],
            ['url' => 'https://staging.example.com'],
        ]);

        $this->assertSame(
            [
                ['url' => self::BASE_URL, 'description' => 'Production'],
                ['url' => 'https://sandbox.example.com', 'description' => 'Sandbox'],
                ['url' => 'https://staging.example.com', 'description' => null],
            ],
            $servers,
        );
    }

    public function testTheProjectUrlIsListedOnceAndKeepsItsOwnDescription(): void
    {
        $servers = OpenApiServers::fromConfig(self::BASE_URL, 'Production', [
            ['url' => self::BASE_URL, 'description' => 'Duplicate'],
            ['url' => 'https://sandbox.example.com', 'description' => 'Sandbox'],
        ]);

        $this->assertSame(
            [
                ['url' => self::BASE_URL, 'description' => 'Production'],
                ['url' => 'https://sandbox.example.com', 'description' => 'Sandbox'],
            ],
            $servers,
        );
    }

    public function testADuplicateWithinTheAdditionalServersIsListedOnce(): void
    {
        $servers = OpenApiServers::fromConfig(self::BASE_URL, 'Production', [
            ['url' => 'https://sandbox.example.com', 'description' => 'Sandbox'],
            ['url' => 'https://sandbox.example.com', 'description' => 'Sandbox again'],
        ]);

        $this->assertSame(
            [
                ['url' => self::BASE_URL, 'description' => 'Production'],
                ['url' => 'https://sandbox.example.com', 'description' => 'Sandbox'],
            ],
            $servers,
        );
    }

    public function testAnEmptyProjectUrlIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        OpenApiServers::fromConfig('', 'Production', []);
    }

    public function testServersThatAreNotAnArrayAreRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The `openapi.servers` config key must be an array.');

        OpenApiServers::fromConfig(self::BASE_URL, 'Production', 'https://sandbox.example.com');
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function malformedEntryProvider(): iterable
    {
        yield 'not an array' => ['https://sandbox.example.com'];
        yield 'missing url' => [['description' => 'Sandbox']];
        yield 'empty url' => [['url' => '', 'description' => 'Sandbox']];
        yield 'url not a string' => [['url' => 42, 'description' => 'Sandbox']];
    }

    #[DataProvider('malformedEntryProvider')]
    public function testAnEntryWithoutAUrlIsRejected(mixed $entry): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The `openapi.servers` entry "0" needs a non-empty string `url`.');

        OpenApiServers::fromConfig(self::BASE_URL, 'Production', [$entry]);
    }

    public function testADescriptionThatIsNotAStringIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The `openapi.servers` entry "0" has a `description` that is not a string.');

        OpenApiServers::fromConfig(self::BASE_URL, 'Production', [
            ['url' => 'https://sandbox.example.com', 'description' => ['Sandbox']],
        ]);
    }
}
