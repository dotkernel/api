<?php

declare(strict_types=1);

namespace App\Template;

use Api\App\Exception\RuntimeException;
use Api\App\Template\Parser;
use Api\App\Template\ParserInterface;
use Api\App\Template\Renderer;
use Api\App\Template\RendererInterface;
use Core\App\Message;
use Core\User\Entity\User;
use Mezzio\Helper\UrlHelperInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

class RendererTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testWillInitiate(): void
    {
        $renderer = new Renderer(
            $this->createMock(ParserInterface::class),
            []
        );

        $this->assertContainsOnlyInstancesOf(RendererInterface::class, [$renderer]);
    }

    /**
     * @throws Exception
     */
    public function testWillPopulatePropertiesFromConfig(): void
    {
        $templates = [
            'test' => 'test',
        ];

        $renderer = new Renderer(
            $this->createMock(ParserInterface::class),
            [
                'templates'              => $templates,
                RendererInterface::class => [
                    'extension' => 'phtml',
                ],
            ],
        );
        $this->assertSame('phtml', $renderer->getExtension());
        $this->assertSame($templates, $renderer->getTemplates());
    }

    /**
     * @throws Exception
     */
    public function testWillThrowErrorOnUnregisteredTemplateNamespace(): void
    {
        $templates = [
            'user' => 'test',
        ];

        $renderer = new Renderer(
            $this->createMock(ParserInterface::class),
            [
                'templates'              => $templates,
                RendererInterface::class => [
                    'extension' => 'phtml',
                ],
            ],
        );

        $this->expectExceptionObject(RuntimeException::create(Message::templateNotFound('test::test')));

        $renderer->getPath('test::test');
    }

    /**
     * @throws Exception
     */
    public function testWillThrowErrorWhenTemplateNotFound(): void
    {
        $templates = [
            'user' => __DIR__ . '/../../../../src/User/templates/user',
        ];

        $renderer = new Renderer(
            $this->createMock(ParserInterface::class),
            [
                'templates'              => $templates,
                RendererInterface::class => [
                    'extension' => 'phtml',
                ],
            ],
        );

        $this->expectExceptionObject(RuntimeException::create(Message::templateNotFound('user::not-found')));

        $renderer->getPath('user::not-found');
    }

    /**
     * @throws Exception
     */
    public function testWillRenderTemplate(): void
    {
        $config = [
            'templates'              => [
                'user' => __DIR__ . '/../../../../src/User/templates/user',
            ],
            RendererInterface::class => [
                'extension' => 'phtml',
            ],
        ];

        $renderer = new Renderer(
            new Parser(
                $this->createMock(UrlHelperInterface::class),
                $config
            ),
            $config,
        );

        $body = $renderer->render('user::welcome', ['user' => (new User())->setIdentity('test')]);

        $this->assertIsString($body);
        $this->assertStringContainsString('Hi test,', $body);
    }
}
