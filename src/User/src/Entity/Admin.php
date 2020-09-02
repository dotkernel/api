<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Api\App\Common\Entity\AbstractEntity;

use function array_map;

/**
 * Class Admin
 * @ORM\Entity(repositoryClass="Api\User\Repository\AdminRepository")
 * @ORM\Table(name="admin")
 * @ORM\HasLifecycleCallbacks()
 * @package Api\User\Entity
 */
class Admin extends AbstractEntity
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE
    ];

    /**
     * @ORM\Column(name="identity", type="string", length=100, nullable=false, unique=true)
     * @var string $identity
     */
    protected string $identity;

    /**
     * @ORM\Column(name="firstName", type="string", length=255)
     * @var $firstName
     */
    protected $firstName;

    /**
     * @ORM\Column(name="lastName", type="string", length=255)
     * @var $lastName
     */
    protected $lastName;

    /**
     * @ORM\Column(name="password", type="string", length=100, nullable=false)
     * @var string $password
     */
    protected string $password;

    /**
     * @ORM\Column(name="status", type="string", length=20, columnDefinition="ENUM('pending', 'active')")
     * @var string $status
     */
    protected string $status = self::STATUS_ACTIVE;

    /**
     * @ORM\ManyToMany(targetEntity="Api\User\Entity\AdminRole")
     * @ORM\JoinTable(
     *     name="admin_roles",
     *     joinColumns={@ORM\JoinColumn(name="userUuid", referencedColumnName="uuid")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="roleUuid", referencedColumnName="uuid")}
     * )
     * @var ArrayCollection $roles
     */
    protected $roles = [];

    /**
     * Admin constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->roles = new ArrayCollection();
    }

    /**
     * @return array
     */
    public function getArrayCopy(): array
    {
        return [
            'uuid' => $this->getUuid()->toString(),
            'identity' => $this->getIdentity(),
            'firstName' => $this->getfirstName(),
            'lastName' => $this->getlastName(),
            'status' => $this->getStatus(),
            'roles' => array_map(function (AdminRole $role) {
                return $role->toArray();
            }, $this->getRoles()->getIterator()->getArrayCopy()),
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated()
        ];
    }

    /**
     * @return string
     */
    public function getIdentity(): string
    {
        return $this->identity;
    }

    /**
     * @param string $identity
     */
    public function setIdentity(string $identity): void
    {
        $this->identity = $identity;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return array|ArrayCollection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param ArrayCollection $roles
     */
    public function setRoles(ArrayCollection $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @param AdminRole $role
     * @return Admin
     */
    public function addRole(AdminRole $role): Admin
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * @param AdminRole $role
     * @return Admin
     */
    public function removeRole(AdminRole $role): Admin
    {
        if (!$this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function resetRoles()
    {
        foreach ($this->roles->getIterator()->getArrayCopy() as $role) {
            $this->removeRole($role);
        }
        $this->roles = new ArrayCollection();

        return $this;
    }
}
