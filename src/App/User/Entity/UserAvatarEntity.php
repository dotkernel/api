<?php

declare(strict_types=1);

namespace App\User\Entity;

use App\Common\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * Class UserAvatarEntity
 * @ORM\Entity()
 * @ORM\Table(name="user_avatar")
 * @package App\User\Entity
 */
class UserAvatarEntity extends AbstractEntity implements ArraySerializableInterface
{
    /**
     * @ORM\Column(name="name", type="string", length=255)
     * @var $name
     */
    protected $name;

    /**
     * UserAvatarEntity constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Exchange internal values from provided array
     *
     * @param  array $array
     * @return void
     */
    public function exchangeArray(array $array)
    {
        // TODO: Implement exchangeArray() method.
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
            'url' => null,
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated()
        ];
    }
}
