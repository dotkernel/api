<?php

declare(strict_types=1);

namespace Api\Admin\Entity;

use Api\Admin\Enum\AdminStatusEnum;
use Api\Admin\Repository\AdminRepository;
use Api\App\Entity\AbstractEntity;
use Api\App\Entity\PasswordTrait;
use Api\App\Entity\RoleInterface;
use Api\App\Entity\TimestampsTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\UserEntityInterface;

#[ORM\Entity(repositoryClass: AdminRepository::class)]
#[ORM\Table("admin")]
#[ORM\HasLifecycleCallbacks]
class Admin extends AbstractEntity implements UserEntityInterface
{
    use PasswordTrait;
    use TimestampsTrait;

    #[ORM\Column(name: "identity", type: "string", length: 100, unique: true)]
    protected string $identity = '';

    #[ORM\Column(name: "firstName", type: "string", length: 191)]
    protected string $firstName = '';

    #[ORM\Column(name: "lastName", type: "string", length: 191)]
    protected string $lastName = '';

    #[ORM\Column(name: "password", type: "string", length: 100)]
    protected string $password = '';

    #[ORM\Column(type: 'admin_status_enum', options: ['default' => AdminStatusEnum::Active])]
    protected AdminStatusEnum $status = AdminStatusEnum::Active;

    #[ORM\ManyToMany(targetEntity: AdminRole::class)]
    #[ORM\JoinTable(name: "admin_roles")]
    #[ORM\JoinColumn(name: "userUuid", referencedColumnName: "uuid")]
    #[ORM\InverseJoinColumn(name: "roleUuid", referencedColumnName: "uuid")]
    protected Collection $roles;

    public function __construct()
    {
        parent::__construct();

        $this->created();
        $this->roles = new ArrayCollection();
    }

    public function getArrayCopy(): array
    {
        return [
            'uuid'      => $this->getUuid()->toString(),
            'identity'  => $this->getIdentity(),
            'firstName' => $this->getFirstName(),
            'lastName'  => $this->getLastName(),
            'status'    => $this->getStatus(),
            'roles'     => $this->getRoles()->map(function (AdminRole $role) {
                return $role->getArrayCopy();
            })->toArray(),
            'created'   => $this->getCreated(),
            'updated'   => $this->getUpdated(),
        ];
    }

    public function getIdentity(): string
    {
        return $this->identity;
    }

    public function setIdentity(string $identity): self
    {
        $this->identity = $identity;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPassword(): string
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

    public function getRoles(): Collection
    {
        return $this->roles;
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

    public function hasRoles(): bool
    {
        return $this->roles->count() > 0;
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
        return $this->getIdentity();
    }
}
