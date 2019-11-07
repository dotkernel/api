<?php

declare(strict_types=1);

namespace Api\Device\DTO;

use Zend\Stdlib\ArraySerializableInterface;

/**
 * Class DeviceDTO
 * @package Api\Device\DTO
 */
class DeviceDTO implements ArraySerializableInterface
{
    /** @var string $type */
    protected $type;

    /** @var string $brand */
    protected $brand;

    /** @var string $model */
    protected $model;

    /** @var bool $isMobile */
    protected $isMobile;

    /**
     * DeviceDTO constructor.
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
    public function getBrand(): string
    {
        return $this->brand;
    }

    /**
     * @param string $brand
     * @return $this
     */
    public function setBrand(string $brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * @param string $model
     * @return $this
     */
    public function setModel(string $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMobile(): bool
    {
        return $this->isMobile;
    }

    /**
     * @param bool $isMobile
     * @return $this
     */
    public function setIsMobile(bool $isMobile)
    {
        $this->isMobile = $isMobile;

        return $this;
    }

    /**
     * @param array $data
     */
    public function exchangeArray(array $data)
    {
        $this
            ->setType($data['type'])
            ->setBrand($data['brand'])
            ->setModel($data['model'])
            ->setIsMobile($data['isMobile']);
    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return [
            'type' => $this->getType(),
            'brand' => $this->getBrand(),
            'model' => $this->getModel(),
            'isMobile' => $this->isMobile()
        ];
    }
}
