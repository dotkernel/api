<?php

declare(strict_types=1);

namespace Api\App\Handler;

use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HomeHandler implements RequestHandlerInterface
{
    use HandlerTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     "config"
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected array $config,
    ) {
    }

    public function get(): ResponseInterface
    {
        return $this->jsonResponse(['message' => 'Welcome to DotKernel API!']);
    }
}
