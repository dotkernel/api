<?php

declare(strict_types=1);

namespace Api\App\Factory;

use Api\App\Command\TokenGenerateCommand;
use Api\App\Service\ErrorReportServiceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function assert;

class TokenGenerateCommandFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): TokenGenerateCommand
    {
        $errorReportService = $container->get(ErrorReportServiceInterface::class);
        assert($errorReportService instanceof ErrorReportServiceInterface);

        return new TokenGenerateCommand($errorReportService);
    }
}
