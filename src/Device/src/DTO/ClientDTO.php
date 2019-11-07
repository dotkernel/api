<?php

declare(strict_types=1);

namespace Api\Device\DTO;

use Zend\Stdlib\ArraySerializableInterface;

/**
 * Class ClientDTO
 * @package Api\Device\DTO
 */
class ClientDTO implements ArraySerializableInterface
{
    /** @var string $type */
    protected $type;

    /** @var string $name */
    protected $name;

    /** @var string $engine */
    protected $engine;

    /** @var string $version */
    protected $version;

    /**
     * ClientDTO constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
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
    public function getEngine(): string
    {
        return $this->engine;
    }

    /**
     * @param string $engine
     * @return $this
     */
    public function setEngine(string $engine)
    {
        $this->engine = $engine;

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
     * @param array $data
     */
    public function exchangeArray(array $data)
    {
        $this->setType($data['type'])->setName($data['name'])->setEngine($data['engine'])->setVersion($data['version']);
    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return [
            'type' => $this->getType(),
            'name' => $this->getName(),
            'engine' => $this->getEngine(),
            'version' => $this->getVersion()
        ];
    }
}
