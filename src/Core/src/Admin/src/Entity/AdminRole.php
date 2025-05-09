<?php

declare(strict_types=1);

namespace Core\Admin\Entity;

use BackedEnum;
use Core\Admin\Enum\AdminRoleEnum;
use Core\Admin\Repository\AdminRoleRepository;
use Core\App\Entity\AbstractEntity;
use Core\App\Entity\RoleInterface;
use Core\App\Entity\TimestampsTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @phpstan-import-type RoleType from RoleInterface
 */
#[ORM\Entity(repositoryClass: AdminRoleRepository::class)]
#[ORM\Table(name: 'admin_role')]
#[ORM\HasLifecycleCallbacks]
class AdminRole extends AbstractEntity implements RoleInterface
{
    use TimestampsTrait;

    #[ORM\Column(
        name: 'name',
        type: 'admin_role_enum',
        unique: true,
        enumType: AdminRoleEnum::class,
        options: ['default' => AdminRoleEnum::Admin]
    )]
    protected AdminRoleEnum $name = AdminRoleEnum::Admin;

    public function __construct()
    {
        parent::__construct();

        $this->created();
    }

    public function getName(): AdminRoleEnum
    {
        return $this->name;
    }

    /**
     * @param AdminRoleEnum $name
     */
    public function setName(BackedEnum $name): self
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
