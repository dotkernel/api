<?php

declare(strict_types=1);

namespace Api\Security;

use Api\Security\Middleware\ErrorResponseMiddleware;
use Dot\DependencyInjection\Factory\AttributedServiceFactory;
use Mezzio\Application;

/**
 * @phpstan-type DependenciesType array{
 *      delegators: array<class-string, class-string[]>,
 *      factories: array<class-string, class-string>,
 * }
 */
class ConfigProvider
{
    /**
     * @return array{
     *     dependencies: DependenciesType,
     * }
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * @return DependenciesType
     */
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
