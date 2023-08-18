<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Entity\AbstractEntity;
use Api\App\Entity\RoleInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Api\User\Repository\UserRoleRepository")
 * @ORM\Table(name="user_role")
 * @ORM\HasLifecycleCallbacks()
 */
class UserRole extends AbstractEntity implements RoleInterface
{
    public const ROLE_GUEST = 'guest';
    public const ROLE_USER  = 'user';
    public const ROLES      = [
        self::ROLE_GUEST,
        self::ROLE_USER,
    ];

    /** @ORM\Column(name="name", type="string", length=20, unique=true) */
    protected string $name;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): RoleInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getArrayCopy(): array
    {
        return [
            'uuid' => $this->getUuid()->toString(),
            'name' => $this->getName(),
        ];
    }
}
