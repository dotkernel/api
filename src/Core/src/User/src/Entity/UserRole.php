<?php

declare(strict_types=1);

namespace Core\User\Entity;

use BackedEnum;
use Core\App\Entity\AbstractEntity;
use Core\App\Entity\RoleInterface;
use Core\App\Entity\TimestampsTrait;
use Core\User\Enum\UserRoleEnum;
use Core\User\Repository\UserRoleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @phpstan-import-type RoleType from RoleInterface
 */
#[ORM\Entity(repositoryClass: UserRoleRepository::class)]
#[ORM\Table(name: 'user_role')]
#[ORM\HasLifecycleCallbacks]
class UserRole extends AbstractEntity implements RoleInterface
{
    use TimestampsTrait;

    #[ORM\Column(
        name: 'name',
        type: 'user_role_enum',
        unique: true,
        enumType: UserRoleEnum::class,
        options: ['default' => UserRoleEnum::User]
    )]
    protected UserRoleEnum $name = UserRoleEnum::User;

    public function __construct()
    {
        parent::__construct();

        $this->created();
    }

    public function getName(): UserRoleEnum
    {
        return $this->name;
    }

    /**
     * @param UserRoleEnum $name
     */
    public function setName(BackedEnum $name): RoleInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return RoleType
     */
    public function getArrayCopy(): array
    {
        return [
            'uuid'    => $this->uuid->toString(),
            'name'    => $this->name->value,
            'created' => $this->created,
            'updated' => $this->updated,
        ];
    }
}
