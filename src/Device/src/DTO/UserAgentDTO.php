<?php

declare(strict_types=1);

namespace Api\Device\DTO;

use Zend\Stdlib\ArraySerializableInterface;

/**
 * Class UserAgentEntity
 * @package Api\Device\DTO
 */
class UserAgentDTO implements ArraySerializableInterface
{
    /** @var string $userAgent */
    protected $userAgent;

    /** @var OSDTO $os */
    protected $os;

    /** @var ClientDTO $client */
    protected $client;

    /** @var DeviceDTO $device */
    protected $device;

    /**
     * UserAgentEntity constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * @param string $userAgent
     * @return $this
     */
    public function setUserAgent(string $userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * @return OSDTO
     */
    public function getOs(): OSDTO
    {
        return $this->os;
    }

    /**
     * @param OSDTO $os
     * @return $this
     */
    public function setOs(OSDTO $os)
    {
        $this->os = $os;

        return $this;
    }

    /**
     * @return ClientDTO
     */
    public function getClient(): ClientDTO
    {
        return $this->client;
    }

    /**
     * @param ClientDTO $client
     * @return $this
     */
    public function setClient(ClientDTO $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return DeviceDTO
     */
    public function getDevice(): DeviceDTO
    {
        return $this->device;
    }

    /**
     * @param DeviceDTO $device
     * @return $this
     */
    public function setDevice(DeviceDTO $device)
    {
        $this->device = $device;

        return $this;
    }

    /**
     * @param array $array
     */
    public function exchangeArray(array $array)
    {
    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return [
            'userAgent' => $this->getUserAgent(),
            'device' => $this->getDevice()->getArrayCopy(),
            'os' => $this->getOs()->getArrayCopy(),
            'client' => $this->getClient()->getArrayCopy()
        ];
    }
}
