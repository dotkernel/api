<?php

declare(strict_types=1);

namespace Core\Admin\Entity;

use Core\Admin\Repository\AdminRoleRepository;
use Core\App\Entity\AbstractEntity;
use Core\App\Entity\RoleInterface;
use Core\App\Entity\TimestampsTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminRoleRepository::class)]
#[ORM\Table('admin_role')]
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

    #[ORM\Column(name: 'name', type: 'string', length: 30, unique: true)]
    protected string $name = '';

    public function __construct()
    {
        parent::__construct();

        $this->created();
    }

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
