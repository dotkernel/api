<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Entity\AbstractEntity;
use Api\App\Entity\TimestampsTrait;
use Api\User\Enum\UserResetPasswordStatusEnum;
use Api\User\Repository\UserResetPasswordRepository;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Exception;

#[ORM\Entity(repositoryClass: UserResetPasswordRepository::class)]
#[ORM\Table(name: "user_reset_password")]
#[ORM\HasLifecycleCallbacks]
class UserResetPassword extends AbstractEntity
{
    use TimestampsTrait;

    #[ORM\ManyToOne(targetEntity: User::class, cascade: ['persist', 'remove'], inversedBy: "resetPasswords")]
    #[ORM\JoinColumn(name: "userUuid", referencedColumnName: "uuid")]
    protected User $user;

    #[ORM\Column(name: "expires", type: "datetime_immutable")]
    protected DateTimeImmutable $expires;

    #[ORM\Column(name: "hash", type: "string", length: 64, unique: true)]
    protected string $hash;

    #[ORM\Column(
        type: 'user_reset_password_status_enum',
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
    }

    public function getUser(): User
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
        } catch (Exception) {
            return false;
        }
    }

    public function markAsCompleted(): self
    {
        $this->status = UserResetPasswordStatusEnum::Completed;

        return $this;
    }

    public function getArrayCopy(): array
    {
        return [
            'uuid'    => $this->getUuid()->toString(),
            'expires' => $this->getExpires(),
            'hash'    => $this->getHash(),
            'status'  => $this->getStatus(),
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated(),
        ];
    }
}
