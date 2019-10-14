<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Common\AbstractEntity;
use Api\App\Common\UuidOrderedTimeGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Zend\Stdlib\ArraySerializableInterface;
use Exception;

use function array_map;
use function bin2hex;
use function random_bytes;

/**
 * Class UserEntity
 * @ORM\Entity(repositoryClass="Api\User\Repository\UserRepository")
 * @ORM\Table(name="user")
 * @ORM\HasLifecycleCallbacks()
 * @package Api\User\Entity
 */
class UserEntity extends AbstractEntity implements ArraySerializableInterface
{
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACTIVE
    ];

    /**
     * @ORM\Column(name="username", type="string", length=255, nullable=false)
     * @var string $email
     */
    protected $email;

    /**
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     * @var string $password
     */
    protected $password;

    /**
     * @ORM\Column(name="status", type="string", nullable=false)
     * @var string $status
     */
    protected $status = self::STATUS_PENDING;

    /**
     * @ORM\Column(name="isDeleted", type="boolean")
     * @var bool $isDeleted
     */
    protected $isDeleted = false;

    /**
     * @ORM\Column(name="hash", type="string", nullable=false)
     * @var string $status
     */
    protected $hash;

    /**
     * @ORM\OneToOne(targetEntity="UserAvatarEntity", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="avatarUuid", referencedColumnName="uuid", nullable=true, onDelete="CASCADE")
     * @var UserAvatarEntity $avatar
     */
    protected $avatar;

    /**
     * @ORM\OneToOne(targetEntity="UserDetailEntity", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="detailUuid", referencedColumnName="uuid", nullable=true, onDelete="CASCADE")
     * @var UserDetailEntity $detail
     */
    protected $detail;

    /**
     * @ORM\OneToMany(targetEntity="UserResetPasswordEntity",
     *     cascade={"persist", "remove"}, mappedBy="user", fetch="EXTRA_LAZY")
     * @var UserResetPasswordEntity[] $resetPassword
     */
    protected $resetPasswords;

    /**
     * @ORM\ManyToMany(targetEntity="UserRoleEntity", fetch="EAGER")
     * @ORM\JoinTable(
     *     name="user_roles",
     *     joinColumns={@ORM\JoinColumn(name="userUuid", referencedColumnName="uuid")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="roleUuid", referencedColumnName="uuid")}
     * )
     * @var UserRoleEntity[] $roles
     */
    protected $roles;

    /**
     * UserEntity constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->roles = new ArrayCollection();
        $this->resetPasswords = new ArrayCollection();

        $this->renewHash();
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
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
    public function setIsDeleted(bool $isDeleted)
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
    public function setHash(string $hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return UserAvatarEntity|null
     */
    public function getAvatar(): ?UserAvatarEntity
    {
        return $this->avatar;
    }

    /**
     * @param UserAvatarEntity|null $avatar
     * @return $this
     */
    public function setAvatar(?UserAvatarEntity $avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return UserDetailEntity|null
     */
    public function getDetail(): ?UserDetailEntity
    {
        return $this->detail;
    }

    /**
     * @param UserDetailEntity $detail
     * @return $this
     */
    public function setDetail(UserDetailEntity $detail)
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * @param UserRoleEntity $role
     * @return $this
     */
    public function addRole(UserRoleEntity $role)
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
     * @param UserRoleEntity $role
     * @return bool
     */
    public function hasRole(UserRoleEntity $role)
    {
        return $this->roles->contains($role);
    }

    /**
     * @param UserRoleEntity $role
     * @return $this
     */
    public function removeRole(UserRoleEntity $role)
    {
        $this->roles->removeElement($role);

        return $this;
    }

    /**
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles)
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
    public function createResetPassword()
    {
        $resetPassword = new UserResetPasswordEntity();
        $resetPassword->setHash($this->generateHash());
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
    public function hasResetPassword(UserResetPasswordEntity $resetPassword)
    {
        return $this->resetPasswords->contains($resetPassword);
    }

    /**
     * @param UserResetPasswordEntity $resetPassword
     * @return $this
     */
    public function removeResetPassword(UserResetPasswordEntity $resetPassword)
    {
        $this->resetPasswords->removeElement($resetPassword);

        return $this;
    }

    /**
     * @param array $resetPasswords
     * @return $this
     */
    public function setResetPasswords(array $resetPasswords)
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
    public function activate()
    {
        return $this->setStatus(self::STATUS_ACTIVE);
    }

    /**
     * @return $this
     */
    public function deactivate()
    {
        return $this->setStatus(self::STATUS_PENDING);
    }

    /**
     * @return string
     */
    public function generateHash(): string
    {
        try {
            $bytes = random_bytes(32);
        } catch (Exception $exception) {
            $bytes = UuidOrderedTimeGenerator::generateUuid()->getBytes();
        }

        return bin2hex($bytes);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getDetail()->getFirstname() . ' ' . $this->getDetail()->getLastname();
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * @return $this
     */
    public function markAsDeleted()
    {
        $this->isDeleted = true;

        return $this;
    }

    /**
     * @return $this
     */
    public function renewHash()
    {
        $this->hash = $this->generateHash();

        return $this;
    }

    /**
     * @return $this
     */
    public function resetRoles()
    {
        foreach ($this->roles->getIterator()->getArrayCopy() as $role) {
            $this->removeRole($role);
        }
        $this->roles = new ArrayCollection();

        return $this;
    }

    /**
     * Return an array representation of the object
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return [
            'uuid' => $this->getUuid()->toString(),
            'hash' => $this->getHash(),
            'email' => $this->getEmail(),
            'status' => $this->getStatus(),
            'isDeleted' => $this->isDeleted(),
            'avatar' => ($this->getAvatar() instanceof UserAvatarEntity) ? $this->getAvatar()->getArrayCopy() : null,
            'detail' => ($this->getDetail() instanceof UserDetailEntity) ? $this->getDetail()->getArrayCopy() : null,
            'roles' => array_map(function (UserRoleEntity $role) {
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
