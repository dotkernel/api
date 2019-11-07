<?php

declare(strict_types=1);

namespace Api\Device\DTO;

use Zend\Stdlib\ArraySerializableInterface;

/**
 * Class OSData
 * @package Api\Device\DTO
 */
class OSDTO implements ArraySerializableInterface
{
    /** @var string $name */
    protected $name;

    /** @var string $version */
    protected $version;

    /** @var string $platform */
    protected $platform;

    /**
     * OSDTO constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     * @return $this
     */
    public function setVersion(string $version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlatform(): string
    {
        return $this->platform;
    }

    /**
     * @param string $platform
     * @return $this
     */
    public function setPlatform(string $platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * @param array $data
     */
    public function exchangeArray(array $data)
    {
        $this->setName($data['name'])->setVersion($data['version'])->setPlatform($data['platform']);
    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return [
            'name' => $this->getName(),
            'version' => $this->getVersion(),
            'platform' => $this->getPlatform()
        ];
    }
}
