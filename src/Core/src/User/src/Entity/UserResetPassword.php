<?php

declare(strict_types=1);

namespace Core\User\Entity;

use Core\App\Entity\AbstractEntity;
use Core\App\Entity\TimestampsTrait;
use Core\User\Enum\UserResetPasswordStatusEnum;
use Core\User\Repository\UserResetPasswordRepository;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Throwable;

#[ORM\Entity(repositoryClass: UserResetPasswordRepository::class)]
#[ORM\Table(name: 'user_reset_password')]
#[ORM\HasLifecycleCallbacks]
class UserResetPassword extends AbstractEntity
{
    use TimestampsTrait;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist', 'remove'], inversedBy: 'resetPasswords')]
    #[ORM\JoinColumn(name: 'userUuid', referencedColumnName: 'uuid')]
    protected ?User $user = null;

    #[ORM\Column(name: 'expires', type: 'datetime_immutable')]
    protected DateTimeImmutable $expires;

    /** @var non-empty-string $hash */
    #[ORM\Column(name: 'hash', type: 'string', length: 191, unique: true)]
    protected string $hash;

    #[ORM\Column(
        type: 'user_reset_password_status_enum',
        enumType: UserResetPasswordStatusEnum::class,
        options: ['default' => UserResetPasswordStatusEnum::Requested],
    )]
    protected UserResetPasswordStatusEnum $status = UserResetPasswordStatusEnum::Requested;

    public function __construct()
    {
        parent::__construct();

        $this->created();
        $this->expires = DateTimeImmutable::createFromMutable(
            (new DateTime())->add(new DateInterval('P1D'))
        );
        $this->hash    = User::generateHash();
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getExpires(): DateTimeImmutable
    {
        return $this->expires;
    }

    public function setExpires(DateTimeImmutable $expires): self
    {
        $this->expires = $expires;

        return $this;
    }

    public function getHash(): string
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

    public function getStatus(): UserResetPasswordStatusEnum
    {
        return $this->status;
    }

    public function setStatus(UserResetPasswordStatusEnum $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->getStatus() === UserResetPasswordStatusEnum::Completed;
    }

    public function isValid(): bool
    {
        try {
            return $this->getExpires() > new DateTimeImmutable();
        } catch (Throwable) {
            return false;
        }
    }

    public function markAsCompleted(): self
    {
        $this->status = UserResetPasswordStatusEnum::Completed;

        return $this;
    }

    /**
     * @return array{
     *     uuid: non-empty-string,
     *     expires: DateTimeImmutable,
     *     hash: non-empty-string,
     *     status: 'completed'|'requested',
     *     created: DateTimeImmutable,
     *     updated: DateTimeImmutable|null
     * }
     */
    public function getArrayCopy(): array
    {
        return [
            'uuid'    => $this->uuid->toString(),
            'expires' => $this->expires,
            'hash'    => $this->hash,
            'status'  => $this->status->value,
            'created' => $this->created,
            'updated' => $this->updated,
        ];
    }
}
