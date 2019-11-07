<?php

declare(strict_types=1);

namespace Api\Device\Service;

use Api\Device\DTO\ClientDTO;
use Api\Device\DTO\DeviceDTO;
use Api\Device\DTO\OSDTO;
use Api\Device\DTO\UserAgentDTO;
use DeviceDetector\DeviceDetector;
use Dot\AnnotatedServices\Annotation\Inject;
use Dot\AnnotatedServices\Annotation\Service;

/**
 * Class DeviceService
 * @package Api\Device\Service
 *
 * @Service
 */
class DeviceService
{
    /** @var DeviceDetector $deviceDetector */
    protected $deviceDetector;

    /**
     * DeviceService constructor.
     * @param DeviceDetector $deviceDetector
     *
     * @Inject({DeviceDetector::class})
     */
    public function __construct(DeviceDetector $deviceDetector)
    {
        $this->deviceDetector = $deviceDetector;
    }

    /**
     * @param string $userAgent
     * @return UserAgentDTO
     */
    public function identify(string $userAgent): UserAgentDTO
    {
        $this->deviceDetector->setUserAgent($userAgent);
        $this->deviceDetector->parse();

        $deviceDTO = new DeviceDTO();
        $deviceDTO
            ->setType($this->deviceDetector->getDeviceName())
            ->setBrand($this->deviceDetector->getBrandName())
            ->setModel($this->deviceDetector->getModel())
            ->setIsMobile($this->deviceDetector->isMobile());

        $clientDTO = new ClientDTO();
        $clientDTO->exchangeArray($this->deviceDetector->getClient());

        $osDTO = new OSDTO();
        $osDTO->exchangeArray($this->deviceDetector->getOs());

        $userAgentDTO = new UserAgentDTO();
        $userAgentDTO->setUserAgent($userAgent)->setOs($osDTO)->setClient($clientDTO)->setDevice($deviceDTO);

        return $userAgentDTO;
    }
}
