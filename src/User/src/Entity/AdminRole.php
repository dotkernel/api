<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Api\App\Common\Entity\AbstractEntity;

/**
 * Class AdminRole
 * @ORM\Entity(repositoryClass="Api\User\Repository\AdminRoleRepository")
 * @ORM\Table(name="admin_role")
 * @ORM\HasLifecycleCallbacks()
 * @package Api\User\Entity
 */
class AdminRole extends AbstractEntity
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPERUSER = 'superuser';
    public const ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_SUPERUSER
    ];

    /**
     * @ORM\Column(name="name", type="string", length=30, nullable=false, unique=true)
     * @var string $name
     */
    protected string $name;

    /**
     * AdminRole constructor.
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
     * @return AdminRole
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'uuid' => $this->getUuid()->toString(),
            'name' => $this->getName(),
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated()
        ];
    }
}
