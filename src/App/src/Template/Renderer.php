<?php

declare(strict_types=1);

namespace Api\App\Template;

use Api\App\Exception\RuntimeException;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;

use function array_key_exists;
use function explode;
use function is_readable;
use function is_string;
use function ob_get_clean;
use function ob_start;
use function realpath;
use function sprintf;

class Renderer implements RendererInterface
{
    protected array $templates = [];
    protected string $extension;

    #[Inject(
        ParserInterface::class,
        'config',
    )]
    public function __construct(
        protected ParserInterface $parser,
        array $config = [],
    ) {
        $this->templates = $config['templates'] ?? [];
        $this->extension = $config[RendererInterface::class]['extension'] ?? 'phtml';
    }

    public function render(string $template, array $params = []): false|string
    {
        ob_start();
        ($this->parser)($this->getPath($template), $params);
        return ob_get_clean();
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @throws RuntimeException
     */
    public function getPath(string $template): string
    {
        [$namespace, $templateName] = explode('::', $template, 2);
        if (! array_key_exists($namespace, $this->templates)) {
            throw RuntimeException::create(Message::templateNotFound($template));
        }

        $path = realpath(sprintf('%s/%s.%s', $this->templates[$namespace], $templateName, $this->extension));
        if (! is_string($path) || ! is_readable($path)) {
            throw RuntimeException::create(Message::templateNotFound($template));
        }

        return $path;
    }

    public function getTemplates(): array
    {
        return $this->templates;
    }
}
