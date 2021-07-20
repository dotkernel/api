<?php

declare(strict_types=1);

namespace Api\Admin\Entity;

use Api\App\Entity\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Exception;

use function array_map;

/**
 * Class Admin
 * @ORM\Entity(repositoryClass="Api\Admin\Repository\AdminRepository")
 * @ORM\Table(name="admin")
 * @ORM\HasLifecycleCallbacks()
 * @package Api\Admin\Entity
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
     * @ORM\Column(name="status", type="string", length=20, nullable=false)
     * @var string $status
     */
    protected string $status = self::STATUS_ACTIVE;

    /**
     * @ORM\ManyToMany(targetEntity="Api\Admin\Entity\AdminRole")
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
     * @throws Exception
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
                return $role->getArrayCopy();
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
     * @return $this
     */
    public function setIdentity(string $identity): self
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param $firstName
     * @return $this
     */
    public function setFirstName($firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param $lastName
     * @return $this
     */
    public function setLastName($lastName): self
    {
        $this->lastName = $lastName;

        return $this;
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
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
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
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
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
     * @return $this
     */
    public function setRoles(ArrayCollection $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @param AdminRole $role
     * @return $this
     */
    public function addRole(AdminRole $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * @param AdminRole $role
     * @return $this
     */
    public function removeRole(AdminRole $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function resetRoles(): self
    {
        foreach ($this->roles->getIterator()->getArrayCopy() as $role) {
            $this->removeRole($role);
        }
        $this->roles = new ArrayCollection();

        return $this;
    }
}
