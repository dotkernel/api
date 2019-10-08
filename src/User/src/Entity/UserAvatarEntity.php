<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Common\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * Class UserAvatarEntity
 * @ORM\Entity()
 * @ORM\Table(name="user_avatar")
 * @ORM\HasLifecycleCallbacks()
 * @package Api\User\Entity
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
