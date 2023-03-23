<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Api\App\Command\TokenGenerateCommand;
use Api\App\Service\ErrorReportServiceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class TokenGenerateCommandFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TokenGenerateCommand
    {
        return new TokenGenerateCommand(
            $container->get(ErrorReportServiceInterface::class)
        );
    }
}
