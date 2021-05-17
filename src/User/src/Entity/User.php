<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Entity\AbstractEntity;
use Api\App\Entity\UuidOrderedTimeGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Throwable;

use function array_map;
use function bin2hex;
use function random_bytes;

/**
 * Class User
 * @ORM\Entity(repositoryClass="Api\User\Repository\UserRepository")
 * @ORM\Table(name="user")
 * @ORM\HasLifecycleCallbacks()
 * @package Api\User\Entity
 */
class User extends AbstractEntity
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACTIVE
    ];

    /**
     * @ORM\OneToOne(targetEntity="UserAvatar", cascade={"persist", "remove"}, mappedBy="user")
     * @var UserAvatar $avatar
     */
    protected $avatar;

    /**
     * @ORM\OneToOne(targetEntity="UserDetail", cascade={"persist", "remove"}, mappedBy="user")
     * @var UserDetail $detail
     */
    protected $detail;

    /**
     * @ORM\OneToMany(targetEntity="UserResetPasswordEntity", cascade={"persist", "remove"}, mappedBy="user")
     * @var UserResetPasswordEntity[] $resetPassword
     */
    protected $resetPasswords;

    /**
     * @ORM\ManyToMany(targetEntity="UserRole")
     * @ORM\JoinTable(
     *     name="user_roles",
     *     joinColumns={@ORM\JoinColumn(name="userUuid", referencedColumnName="uuid")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="roleUuid", referencedColumnName="uuid")}
     * )
     * @var UserRole[] $roles
     */
    protected $roles;

    /**
     * @ORM\Column(name="identity", type="string", length=191, nullable=false, unique=true)
     * @var string $identity
     */
    protected $identity;

    /**
     * @ORM\Column(name="password", type="string", length=191, nullable=false)
     * @var string $password
     */
    protected $password;

    /**
     * @ORM\Column(name="status", type="string", length=20, nullable=false)
     * @var string $status
     */
    protected $status = self::STATUS_PENDING;

    /**
     * @ORM\Column(name="isDeleted", type="boolean")
     * @var bool $isDeleted
     */
    protected $isDeleted = false;

    /**
     * @ORM\Column(name="hash", type="string", length=64, nullable=false, unique=true)
     * @var string $hash
     */
    protected $hash;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->roles = new ArrayCollection();
        $this->resetPasswords = new ArrayCollection();

        $this->renewHash();
    }

    /**
     * @return string
     */
    public function getIdentity()
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
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $password
     * @return $this
     */
    public function setPassword($password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    /**
     * @param bool $isDeleted
     * @return $this
     */
    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return $this
     */
    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return UserAvatar|null
     */
    public function getAvatar(): ?UserAvatar
    {
        return $this->avatar;
    }

    /**
     * @param UserAvatar|null $avatar
     * @return $this
     */
    public function setAvatar(?UserAvatar $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return $this
     */
    public function removeAvatar(): self
    {
        $this->avatar = null;

        return $this;
    }

    /**
     * @return UserDetail|null
     */
    public function getDetail(): ?UserDetail
    {
        return $this->detail;
    }

    /**
     * @param UserDetail $detail
     * @return $this
     */
    public function setDetail(UserDetail $detail): self
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * @param UserRole $role
     * @return $this
     */
    public function addRole(UserRole $role): self
    {
        $this->roles->add($role);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param UserRole $role
     * @return bool
     */
    public function hasRole(UserRole $role): bool
    {
        return $this->roles->contains($role);
    }

    /**
     * @param UserRole $role
     * @return $this
     */
    public function removeRole(UserRole $role): self
    {
        $this->roles->removeElement($role);

        return $this;
    }

    /**
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        foreach ($roles as $role) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * @param UserResetPasswordEntity $resetPassword
     */
    public function addResetPassword(UserResetPasswordEntity $resetPassword)
    {
        $this->resetPasswords->add($resetPassword);
    }

    /**
     * @return $this
     */
    public function createResetPassword(): self
    {
        $resetPassword = new UserResetPasswordEntity();
        $resetPassword->setHash(self::generateHash());
        $resetPassword->setUser($this);

        $this->resetPasswords->add($resetPassword);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getResetPasswords()
    {
        return $this->resetPasswords;
    }

    /**
     * @param UserResetPasswordEntity $resetPassword
     * @return bool
     */
    public function hasResetPassword(UserResetPasswordEntity $resetPassword): bool
    {
        return $this->resetPasswords->contains($resetPassword);
    }

    /**
     * @param UserResetPasswordEntity $resetPassword
     * @return $this
     */
    public function removeResetPassword(UserResetPasswordEntity $resetPassword): self
    {
        $this->resetPasswords->removeElement($resetPassword);

        return $this;
    }

    /**
     * @param array $resetPasswords
     * @return $this
     */
    public function setResetPasswords(array $resetPasswords): self
    {
        foreach ($resetPasswords as $resetPassword) {
            $this->resetPasswords->add($resetPassword);
        }

        return $this;
    }

    /**
     * Helper methods
     */

    /**
     * @return $this
     */
    public function activate(): self
    {
        return $this->setStatus(self::STATUS_ACTIVE);
    }

    /**
     * @return $this
     */
    public function deactivate(): self
    {
        return $this->setStatus(self::STATUS_PENDING);
    }

    /**
     * @return string
     */
    public static function generateHash(): string
    {
        try {
            $bytes = random_bytes(32);
        } catch (Throwable $exception) {
            $bytes = UuidOrderedTimeGenerator::generateUuid()->getBytes();
        }

        return bin2hex($bytes);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getDetail()->getFirstname() . ' ' . $this->getDetail()->getLastname();
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * @return $this
     */
    public function markAsDeleted(): self
    {
        $this->isDeleted = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function renewHash(): self
    {
        $this->hash = self::generateHash();

        return $this;
    }

    /**
     * @return $this
     * @throws Throwable
     */
    public function resetRoles(): self
    {
        foreach ($this->roles->getIterator()->getArrayCopy() as $role) {
            $this->removeRole($role);
        }
        $this->roles = new ArrayCollection();

        return $this;
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function getArrayCopy(): array
    {
        return [
            'uuid' => $this->getUuid()->toString(),
            'hash' => $this->getHash(),
            'identity' => $this->getIdentity(),
            'status' => $this->getStatus(),
            'isDeleted' => $this->isDeleted(),
            'avatar' => ($this->getAvatar() instanceof UserAvatar) ? $this->getAvatar()->getArrayCopy() : null,
            'detail' => ($this->getDetail() instanceof UserDetail) ? $this->getDetail()->getArrayCopy() : null,
            'roles' => array_map(function (UserRole $role) {
                return $role->getArrayCopy();
            }, $this->getRoles()->getIterator()->getArrayCopy()),
            'resetPasswords' => array_map(function (UserResetPasswordEntity $resetPassword) {
                return $resetPassword->getArrayCopy();
            }, $this->getResetPasswords()->getIterator()->getArrayCopy()),
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated()
        ];
    }
}
