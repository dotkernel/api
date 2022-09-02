<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Entity\AbstractEntity;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Throwable;

/**
 * Class UserResetPasswordEntity
 * @ORM\Entity()
 * @ORM\Table(name="user_reset_password")
 * @ORM\HasLifecycleCallbacks()
 * @package Api\User\Entity
 */
class UserResetPasswordEntity extends AbstractEntity
{
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REQUESTED = 'requested';
    public const STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_REQUESTED
    ];

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist", "remove"}, inversedBy="resetPasswords")
     * @ORM\JoinColumn(name="userUuid", referencedColumnName="uuid")
     * @var User $user
     */
    protected User $user;

    /**
     * @ORM\Column(name="expires", type="datetime_immutable")
     * @var DateTimeImmutable $expires
     */
    protected DateTimeImmutable $expires;

    /**
     * @ORM\Column(name="hash", type="string", length=64, unique=true)
     * @var string $hash
     */
    protected string $hash;

    /**
     * @ORM\Column(name="status", type="string", length=20)
     * @var string $status
     */
    protected string $status = self::STATUS_REQUESTED;

    /**
     * UserResetPasswordEntity constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $tomorrow = new DateTime();
        $tomorrow->add(new DateInterval('P1D'));
        $this->expires = DateTimeImmutable::createFromMutable($tomorrow);
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return DateTimeImmutable
     */
    public function getExpires(): DateTimeImmutable
    {
        return $this->expires;
    }

    /**
     * @param DateTimeImmutable $expires
     * @return $this
     */
    public function setExpires(DateTimeImmutable $expires): self
    {
        $this->expires = $expires;

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
     * @param $hash
     * @return $this
     */
    public function setHash($hash): self
    {
        $this->hash = $hash;

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
     * Helper methods
     */

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->getStatus() === self::STATUS_COMPLETED;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        try {
            return $this->getExpires() > (new DateTimeImmutable());
        } catch (Throwable $exception) {
        }

        return false;
    }

    /**
     * @return $this
     */
    public function markAsCompleted(): self
    {
        $this->status = self::STATUS_COMPLETED;

        return $this;
    }

    /**
     * Return an array representation of the object
     *
     * @return array
     */
    public function getArrayCopy(): array
    {
        return [
            'uuid' => $this->getUuid()->toString(),
            'expires' => $this->getExpires(),
            'hash' => $this->getHash(),
            'status' => $this->getStatus(),
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated()
        ];
    }
}
