<?php

declare(strict_types=1);

namespace Core\User\Entity;

use Core\App\Entity\AbstractEntity;
use Core\App\Entity\PasswordTrait;
use Core\App\Entity\RoleInterface;
use Core\App\Entity\TimestampsTrait;
use Core\User\Enum\UserStatusEnum;
use Core\User\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Server\Entities\UserEntityInterface;

use function array_map;
use function bin2hex;
use function md5;
use function trim;
use function uniqid;

/**
 * @phpstan-import-type UserAvatarType from UserAvatar
 * @phpstan-import-type UserDetailType from UserDetail
 * @phpstan-import-type RoleType from RoleInterface
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
#[ORM\HasLifecycleCallbacks]
class User extends AbstractEntity implements UserEntityInterface
{
    use PasswordTrait;
    use TimestampsTrait;

    #[ORM\OneToOne(targetEntity: UserAvatar::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    protected ?UserAvatar $avatar = null;

    #[ORM\OneToOne(targetEntity: UserDetail::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    protected ?UserDetail $detail = null;

    /** @var Collection<int, UserResetPassword> */
    #[ORM\OneToMany(targetEntity: UserResetPassword::class, mappedBy: 'user', cascade: ['persist', 'remove'])]
    protected Collection $resetPasswords;

    /** @var Collection<int, RoleInterface> */
    #[ORM\ManyToMany(targetEntity: UserRole::class)]
    #[ORM\JoinTable(name: 'user_roles')]
    #[ORM\JoinColumn(name: 'userUuid', referencedColumnName: 'uuid')]
    #[ORM\InverseJoinColumn(name: 'roleUuid', referencedColumnName: 'uuid')]
    protected Collection $roles;

    /** @var non-empty-string|null $identity */
    #[ORM\Column(name: 'identity', type: 'string', length: 191, unique: true)]
    protected ?string $identity = null;

    #[ORM\Column(name: 'password', type: 'string', length: 191)]
    protected ?string $password = null;

    #[ORM\Column(
        type: 'user_status_enum',
        enumType: UserStatusEnum::class,
        options: ['default' => UserStatusEnum::Pending]
    )]
    protected UserStatusEnum $status = UserStatusEnum::Pending;

    /** @var non-empty-string $hash */
    #[ORM\Column(name: 'hash', type: 'string', length: 191, unique: true)]
    protected string $hash;

    public function __construct()
    {
        parent::__construct();

        $this->hash           = self::generateHash();
        $this->roles          = new ArrayCollection();
        $this->resetPasswords = new ArrayCollection();

        $this->created();
        $this->renewHash();
    }

    public function getAvatar(): ?UserAvatar
    {
        return $this->avatar;
    }

    public function hasAvatar(): bool
    {
        return $this->avatar instanceof UserAvatar;
    }

    public function removeAvatar(): self
    {
        $this->avatar = null;

        return $this;
    }

    public function setAvatar(?UserAvatar $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getDetail(): ?UserDetail
    {
        return $this->detail;
    }

    public function hasDetail(): bool
    {
        return $this->detail !== null;
    }

    public function setDetail(UserDetail $detail): self
    {
        $this->detail = $detail;

        return $this;
    }

    public function addResetPassword(UserResetPassword $resetPassword): void
    {
        $this->resetPasswords->add($resetPassword);
    }

    /**
     * @return Collection<int, UserResetPassword>
     */
    public function getResetPasswords(): Collection
    {
        return $this->resetPasswords;
    }

    public function hasResetPassword(UserResetPassword $resetPassword): bool
    {
        return $this->resetPasswords->contains($resetPassword);
    }

    public function removeResetPassword(UserResetPassword $resetPassword): self
    {
        $this->resetPasswords->removeElement($resetPassword);

        return $this;
    }

    /**
     * @param array<int, UserResetPassword> $resetPasswords
     */
    public function setResetPasswords(array $resetPasswords): self
    {
        foreach ($resetPasswords as $resetPassword) {
            $this->resetPasswords->add($resetPassword);
        }

        return $this;
    }

    public function addRole(RoleInterface $role): self
    {
        $this->roles->add($role);

        return $this;
    }

    /**
     * @return RoleInterface[]
     */
    public function getRoles(): array
    {
        return $this->roles->toArray();
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

    /**
     * @param RoleInterface[] $roles
     */
    public function setRoles(array $roles): self
    {
        foreach ($roles as $role) {
            $this->roles->add($role);
        }

        return $this;
    }

    public function getIdentity(): ?string
    {
        return $this->identity;
    }

    public function hasIdentity(): bool
    {
        return $this->identity !== null;
    }

    /**
     * @param non-empty-string $identity
     */
    public function setIdentity(string $identity): self
    {
        $this->identity = $identity;

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

    public function getStatus(): UserStatusEnum
    {
        return $this->status;
    }

    public function setStatus(UserStatusEnum $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * @param non-empty-string $hash
     */
    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getIdentifier(): string
    {
        return (string) $this->identity;
    }

    public function activate(): self
    {
        return $this->setStatus(UserStatusEnum::Active);
    }

    public function deactivate(): self
    {
        return $this->setStatus(UserStatusEnum::Pending);
    }

    /**
     * @return non-empty-string
     */
    public static function generateHash(): string
    {
        return bin2hex(md5(uniqid()));
    }

    public function getEmail(): string
    {
        if (! $this->getDetail() instanceof UserDetail) {
            return '';
        }

        return trim((string) $this->getDetail()->getEmail());
    }

    public function hasEmail(): bool
    {
        return $this->getEmail() !== '';
    }

    public function getName(): string
    {
        if (! $this->getDetail() instanceof UserDetail) {
            return '';
        }

        return trim($this->getDetail()->getFirstName() . ' ' . $this->getDetail()->getLastName());
    }

    public function isActive(): bool
    {
        return $this->status === UserStatusEnum::Active;
    }

    public function isPending(): bool
    {
        return $this->status === UserStatusEnum::Pending;
    }

    public function isDeleted(): bool
    {
        return $this->status === UserStatusEnum::Deleted;
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

    /**
     * @return array{
     *     uuid: non-empty-string,
     *     avatar: UserAvatarType|null,
     *     detail: UserDetailType|null,
     *     hash: non-empty-string,
     *     identity: non-empty-string|null,
     *     status: non-empty-string,
     *     roles: RoleType[],
     *     created: DateTimeImmutable,
     *     updated: DateTimeImmutable|null,
     * }
     */
    public function getArrayCopy(): array
    {
        return [
            'uuid'     => $this->uuid->toString(),
            'avatar'   => $this->avatar?->getArrayCopy(),
            'detail'   => $this->detail?->getArrayCopy(),
            'hash'     => $this->hash,
            'identity' => $this->identity,
            'status'   => $this->status->value,
            'roles'    => array_map(fn (RoleInterface $role): array => $role->getArrayCopy(), $this->roles->toArray()),
            'created'  => $this->created,
            'updated'  => $this->updated,
        ];
    }
}
