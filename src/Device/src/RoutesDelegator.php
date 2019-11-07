<?php

declare(strict_types=1);

namespace Api\Device;

use Api\Device\Handler\DeviceHandler;
use Psr\Container\ContainerInterface;
use Zend\Expressive\Application;

/**
 * Class RoutesDelegator
 * @package Api\Device
 */
class RoutesDelegator
{
    /**
     * @param ContainerInterface $container
     * @param $serviceName
     * @param callable $callback
     * @return Application
     */
    public function __invoke(ContainerInterface $container, $serviceName, callable $callback)
    {
        /** @var Application $app */
        $app = $callback();

        /**
         * Device details from user agent.
         */
        $app->put('/device/user-agent', DeviceHandler::class, 'device:user-agent');

        return $app;
    }
}
