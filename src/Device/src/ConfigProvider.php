<?php

declare(strict_types=1);

namespace Api\Device;

use Api\Device\DTO\UserAgentDTO;
use Api\Device\Handler\DeviceHandler;
use Api\Device\Service\DeviceService;
use Dot\AnnotatedServices\Factory\AnnotatedServiceFactory;
use Zend\Expressive\Hal\Metadata\MetadataMap;
use Zend\Expressive\Hal\Metadata\RouteBasedResourceMetadata;
use Zend\Hydrator\ArraySerializable;

/**
 * Class ConfigProvider
 * @package Api\Device
 */
class ConfigProvider
{
    /**
     * @return array
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
            MetadataMap::class => $this->getHalConfig()
        ];
    }

    /**
     * @return array
     */
    public function getDependencies(): array
    {
        return [
            'factories' => [
                DeviceHandler::class => AnnotatedServiceFactory::class,
                DeviceService::class => AnnotatedServiceFactory::class
            ]
        ];
    }

    /**
     * @return array
     */
    public function getHalConfig() : array
    {
        return [
            [
                '__class__' => RouteBasedResourceMetadata::class,
                'resource_class' => UserAgentDTO::class,
                'route' => 'device:user-agent',
                'extractor' => ArraySerializable::class
            ]
        ];
    }
}
