<?php

declare(strict_types=1);

namespace Api\Admin\Entity;

use Api\App\Entity\AbstractEntity;
use Api\App\Entity\RoleInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AdminRole
 * @ORM\Entity(repositoryClass="Api\Admin\Repository\AdminRoleRepository")
 * @ORM\Table(name="admin_role")
 * @ORM\HasLifecycleCallbacks()
 * @package Api\Admin\Entity
 */
class AdminRole extends AbstractEntity implements RoleInterface
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPERUSER = 'superuser';
    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_SUPERUSER
    ];

    /**
     * @ORM\Column(name="name", type="string", length=30, unique=true)
     */
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
