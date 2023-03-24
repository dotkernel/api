<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Entity\AbstractEntity;
use Api\App\Entity\PasswordTrait;
use Api\App\Entity\RoleInterface;
use Api\App\Entity\UuidOrderedTimeGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use League\OAuth2\Server\Entities\UserEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Throwable;

/**
 * Class User
 * @ORM\Entity(repositoryClass="Api\User\Repository\UserRepository")
 * @ORM\Table(name="user")
 * @ORM\HasLifecycleCallbacks()
 * @package Api\User\Entity
 */
class User extends AbstractEntity implements UserEntityInterface
{
    use PasswordTrait;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ACTIVE
    ];

    /**
     * @ORM\OneToOne(targetEntity="UserAvatar", cascade={"persist", "remove"}, mappedBy="user")
     */
    protected ?UserAvatar $avatar = null;

    /**
     * @ORM\OneToOne(targetEntity="UserDetail", cascade={"persist", "remove"}, mappedBy="user")
     */
    protected UserDetail $detail;

    /**
     * @ORM\OneToMany(targetEntity="UserResetPasswordEntity", cascade={"persist", "remove"}, mappedBy="user")
     */
    protected Collection $resetPasswords;

    /**
     * @ORM\ManyToMany(targetEntity="UserRole")
     * @ORM\JoinTable(
     *     name="user_roles",
     *     joinColumns={@ORM\JoinColumn(name="userUuid", referencedColumnName="uuid")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="roleUuid", referencedColumnName="uuid")}
     * )
     */
    protected Collection $roles;

    /**
     * @ORM\Column(name="identity", type="string", length=191, unique=true)
     */
    protected string $identity;

    /**
     * @ORM\Column(name="password", type="string", length=191)
     */
    protected string $password;

    /**
     * @ORM\Column(name="status", type="string", length=20)
     */
    protected string $status = self::STATUS_PENDING;

    /**
     * @ORM\Column(name="isDeleted", type="boolean")
     */
    protected bool $isDeleted = false;

    /**
     * @ORM\Column(name="hash", type="string", length=64, unique=true)
     */
    protected string $hash;

    public function __construct()
    {
        parent::__construct();

        $this->roles = new ArrayCollection();
        $this->resetPasswords = new ArrayCollection();

        $this->renewHash();
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

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @psalm-param 'active'|'pending' $status
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getAvatar(): ?UserAvatar
    {
        return $this->avatar;
    }

    public function setAvatar(?UserAvatar $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function removeAvatar(): self
    {
        $this->avatar = null;

        return $this;
    }

    public function hasAvatar(): bool
    {
        return $this->avatar instanceof UserAvatar;
    }

    public function getDetail(): UserDetail
    {
        return $this->detail;
    }

    public function setDetail(UserDetail $detail): self
    {
        $this->detail = $detail;

        return $this;
    }

    public function getIdentifier(): string
    {
        return $this->getIdentity();
    }

    public function addRole(RoleInterface $role): self
    {
        $this->roles->add($role);

        return $this;
    }

    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function hasRole(RoleInterface $role): bool
    {
        return $this->roles->contains($role);
    }

    public function removeRole(RoleInterface $role): self
    {
        $this->roles->removeElement($role);

        return $this;
    }

    public function setRoles(array $roles): self
    {
        foreach ($roles as $role) {
            $this->roles->add($role);
        }

        return $this;
    }

    public function addResetPassword(UserResetPasswordEntity $resetPassword): void
    {
        $this->resetPasswords->add($resetPassword);
    }

    public function createResetPassword(): self
    {
        $this->resetPasswords->add(
            (new UserResetPasswordEntity())
                ->setHash(self::generateHash())
                ->setUser($this)
        );

        return $this;
    }

    public function getResetPasswords(): Collection
    {
        return $this->resetPasswords;
    }

    public function hasResetPassword(UserResetPasswordEntity $resetPassword): bool
    {
        return $this->resetPasswords->contains($resetPassword);
    }

    public function removeResetPassword(UserResetPasswordEntity $resetPassword): self
    {
        $this->resetPasswords->removeElement($resetPassword);

        return $this;
    }

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

    public function activate(): self
    {
        return $this->setStatus(self::STATUS_ACTIVE);
    }

    public function deactivate(): self
    {
        return $this->setStatus(self::STATUS_PENDING);
    }

    public static function generateHash(): string
    {
        try {
            $bytes = random_bytes(32);
        } catch (Throwable) {
            $bytes = UuidOrderedTimeGenerator::generateUuid()->getBytes();
        }

        return bin2hex($bytes);
    }

    public function getName(): string
    {
        return $this->getDetail()->getFirstName() . ' ' . $this->getDetail()->getLastName();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function markAsDeleted(): self
    {
        $this->isDeleted = true;

        return $this;
    }

    public function renewHash(): self
    {
        $this->hash = self::generateHash();

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

    public function hasEmail(): bool
    {
        return !empty($this->getDetail()->getEmail());
    }

    public function getArrayCopy(): array
    {
        return [
            'uuid' => $this->getUuid()->toString(),
            'hash' => $this->getHash(),
            'identity' => $this->getIdentity(),
            'status' => $this->getStatus(),
            'isDeleted' => $this->isDeleted(),
            'avatar' => $this->getAvatar()?->getArrayCopy(),
            'detail' => $this->getDetail()->getArrayCopy(),
            'roles' => $this->getRoles()->map(function (UserRole $userRole) {
                return $userRole->getArrayCopy();
            })->toArray(),
            'resetPasswords' => $this->getResetPasswords()->map(function (UserResetPasswordEntity $resetPassword) {
                return $resetPassword->getArrayCopy();
            })->toArray(),
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated(),
        ];
    }
}
