<?php

declare(strict_types=1);

namespace App\User\Entity;

use App\Common\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * Class UserRoleEntity
 * @ORM\Entity()
 * @ORM\Table(name="user_role")
 * @package App\User\Entity
 */
class UserRoleEntity extends AbstractEntity implements ArraySerializableInterface
{
    const ROLE_MEMBER = 'member';

    /**
     * @ORM\Column(name="name", type="string")
     * @var string $name
     */
    protected $name;

    /**
     * UserRolesEntity constructor.
     */
    public function __construct()
    {
        parent::__construct();
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
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'uuid' => $this->getUuid()->toString(),
            'name' => $this->getName(),
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated()
        ];
    }

    /**
     * Exchange internal values from provided array
     *
     * @param  array $array
     * @return void
     */
    public function exchangeArray(array $array)
    {
        $this->setName($array['name']);
    }

    /**
     * Return an array representation of the object
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return [
            'uuid' => $this->getUuid()->toString(),
            'name' => $this->getName(),
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated()
        ];
    }
}
