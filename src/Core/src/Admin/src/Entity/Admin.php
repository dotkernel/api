<?php

declare(strict_types=1);

namespace Core\Admin\Entity;

use Core\Admin\Enum\AdminStatusEnum;
use Core\Admin\Repository\AdminRepository;
use Core\App\Entity\AbstractEntity;
use Core\App\Entity\PasswordTrait;
use Core\App\Entity\RoleInterface;
use Core\App\Entity\TimestampsTrait;
use Core\Setting\Entity\Setting;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\UserEntityInterface;

use function array_map;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
#[ORM\Table(name: 'admin')]
#[ORM\HasLifecycleCallbacks]
class Admin extends AbstractEntity implements UserEntityInterface
{
    use PasswordTrait;
    use TimestampsTrait;

    #[ORM\Column(name: 'identity', type: 'string', length: 191, unique: true)]
    protected ?string $identity = null;

    #[ORM\Column(name: 'firstName', type: 'string', length: 191, nullable: true)]
    protected ?string $firstName = null;

    #[ORM\Column(name: 'lastName', type: 'string', length: 191, nullable: true)]
    protected ?string $lastName = null;

    #[ORM\Column(name: 'password', type: 'string', length: 191)]
    protected ?string $password = null;

    #[ORM\Column(
        type: 'admin_status_enum',
        enumType: AdminStatusEnum::class,
        options: ['default' => AdminStatusEnum::Active]
    )]
    protected AdminStatusEnum $status = AdminStatusEnum::Active;

    #[ORM\ManyToMany(targetEntity: AdminRole::class)]
    #[ORM\JoinTable(name: 'admin_roles')]
    #[ORM\JoinColumn(name: 'userUuid', referencedColumnName: 'uuid')]
    #[ORM\InverseJoinColumn(name: 'roleUuid', referencedColumnName: 'uuid')]
    protected Collection $roles;

    #[ORM\OneToMany(targetEntity: Setting::class, mappedBy: 'admin')]
    protected Collection $settings;

    public function __construct()
    {
        parent::__construct();

        $this->created();
        $this->roles    = new ArrayCollection();
        $this->settings = new ArrayCollection();
    }

    public function getIdentity(): ?string
    {
        return $this->identity;
    }

    public function hasIdentity(): bool
    {
        return $this->identity !== null;
    }

    public function setIdentity(string $identity): self
    {
        $this->identity = $identity;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getStatus(): AdminStatusEnum
    {
        return $this->status;
    }

    public function setStatus(AdminStatusEnum $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles->toArray();
    }

    public function setRoles(ArrayCollection $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(RoleInterface $role): self
    {
        if (! $this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    public function hasRole(RoleInterface $role): bool
    {
        return $this->roles->contains($role);
    }

    public function hasRoles(): bool
    {
        return $this->roles->count() > 0;
    }

    public function removeRole(RoleInterface $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }

        return $this;
    }

    public function resetRoles(): self
    {
        $this->roles = new ArrayCollection();

        return $this;
    }

    public function activate(): self
    {
        $this->status = AdminStatusEnum::Active;

        return $this;
    }

    public function deactivate(): self
    {
        $this->status = AdminStatusEnum::Inactive;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->status === AdminStatusEnum::Active;
    }

    public function getIdentifier(): string
    {
        return $this->identity;
    }

    public function getArrayCopy(): array
    {
        return [
            'uuid'      => $this->uuid->toString(),
            'identity'  => $this->identity,
            'firstName' => $this->firstName,
            'lastName'  => $this->lastName,
            'status'    => $this->status->value,
            'roles'     => array_map(fn (AdminRole $role): array => $role->getArrayCopy(), $this->roles->toArray()),
            'created'   => $this->created,
            'updated'   => $this->updated,
        ];
    }
}
