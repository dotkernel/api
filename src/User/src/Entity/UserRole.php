<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Common\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;
use Laminas\Stdlib\ArraySerializableInterface;

/**
 * Class UserRole
 * @ORM\Entity(repositoryClass="Api\User\Repository\UserRoleRepository")
 * @ORM\Table(name="user_role")
 * @ORM\HasLifecycleCallbacks()
 * @package Api\User\Entity
 */
class UserRole extends AbstractEntity implements ArraySerializableInterface
{
    public const ROLE_GUEST = 'guest';
    public const ROLE_USER = 'user';
    public const ROLES = [
        self::ROLE_GUEST,
        self::ROLE_USER,
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
