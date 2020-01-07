<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Common\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Laminas\Stdlib\ArraySerializableInterface;

/**
 * Class UserRoleEntity
 * @ORM\Entity(repositoryClass="Api\User\Repository\UserRoleRepository")
 * @ORM\Table(name="user_role")
 * @ORM\HasLifecycleCallbacks()
 * @package Api\User\Entity
 */
class UserRoleEntity extends AbstractEntity implements ArraySerializableInterface
{
    const ROLE_ADMIN = 'admin';
    const ROLE_MEMBER = 'member';
    const ROLES = [
        self::ROLE_MEMBER,
        self::ROLE_ADMIN
    ];

    /**
     * @ORM\Column(name="name", type="string", length=20, nullable=false, unique=true)
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
