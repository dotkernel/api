<?php

declare(strict_types=1);

namespace Api\Security;

use Api\Security\Middleware\ErrorResponseMiddleware;
use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Mezzio\Application;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    private function getDependencies(): array
    {
        return [
            'delegators' => [
                Application::class => [RoutesDelegator::class],
            ],
            'factories'  => [
                ErrorResponseMiddleware::class => AttributedServiceFactory::class,
            ],
        ];
    }
}
