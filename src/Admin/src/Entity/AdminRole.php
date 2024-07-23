<?php

declare(strict_types=1);

namespace Api\Admin\Entity;

use Api\Admin\Repository\AdminRoleRepository;
use Api\App\Entity\AbstractEntity;
use Api\App\Entity\RoleInterface;
use Api\App\Entity\TimestampsTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminRoleRepository::class)]
#[ORM\Table("admin_role")]
#[ORM\HasLifecycleCallbacks]
class AdminRole extends AbstractEntity implements RoleInterface
{
    use TimestampsTrait;

    public const ROLE_ADMIN     = 'admin';
    public const ROLE_SUPERUSER = 'superuser';
    public const ROLES          = [
        self::ROLE_ADMIN,
        self::ROLE_SUPERUSER,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->created();
    }

    #[ORM\Column(name: "name", type: "string", length: 30, unique: true)]
    protected string $name = '';

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
