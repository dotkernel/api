<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Entity\AbstractEntity;
use Api\App\Entity\RoleInterface;
use Api\App\Entity\TimestampsTrait;
use Api\User\Repository\UserRoleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRoleRepository::class)]
#[ORM\Table(name: "user_role")]
#[ORM\HasLifecycleCallbacks]
class UserRole extends AbstractEntity implements RoleInterface
{
    use TimestampsTrait;

    public const ROLE_GUEST = 'guest';
    public const ROLE_USER  = 'user';
    public const ROLES      = [
        self::ROLE_GUEST,
        self::ROLE_USER,
    ];

    #[ORM\Column(name: "name", type: "string", length: 20, unique: true)]
    protected ?string $name = null;

    public function __construct()
    {
        parent::__construct();

        $this->created();
    }

    public function getName(): ?string
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
