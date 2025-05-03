<?php

declare(strict_types=1);

namespace App\Template;

use Api\App\Template\Parser;
use Api\App\Template\ParserInterface;
use Api\App\Template\RendererInterface;
use Core\User\Entity\User;
use Mezzio\Helper\UrlHelperInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

use function ob_get_clean;
use function ob_start;

class ParserTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testWillInitiate(): void
    {
        $parser = new Parser(
            $this->createMock(UrlHelperInterface::class),
            []
        );

        $this->assertContainsOnlyInstancesOf(ParserInterface::class, [$parser]);
    }

    /**
     * @throws Exception
     */
    public function testWillSetGlobals(): void
    {
        $parser = new Parser(
            $this->createMock(UrlHelperInterface::class),
            []
        );

        $this->assertSame([], $parser->getGlobals());

        $parser = new Parser(
            $this->createMock(UrlHelperInterface::class),
            [
                RendererInterface::class => [
                    'globals' => [
                        'test' => 'test',
                    ],
                ],
            ]
        );

        $this->assertSame(['test' => 'test'], $parser->getGlobals());

        $parser = new Parser(
            $this->createMock(UrlHelperInterface::class),
            [
                RendererInterface::class => [
                    'globals' => [
                        'bar' => 'Bar',
                    ],
                ],
                'application'            => [
                    'foo' => 'Foo',
                ],
            ]
        );

        $this->assertSame([
            'bar'         => 'Bar',
            'application' => [
                'foo' => 'Foo',
            ],
        ], $parser->getGlobals());
    }

    /**
     * @throws Exception
     */
    public function testWillParseVariables(): void
    {
        $parser = new Parser(
            $this->createMock(UrlHelperInterface::class),
            [
                'application' => [
                    'foo' => 'Foo',
                ],
            ]
        );

        ob_start();
        $parser(
            __DIR__ . '/../../../../src/User/templates/user/welcome.phtml',
            [
                'user' => (new User())->setIdentity('test'),
            ]
        );
        $body = ob_get_clean();

        $this->assertIsString($body);
        $this->assertStringContainsString('Hi test,', $body);
    }
}
