<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Common\Entity\AbstractEntity;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Laminas\Stdlib\ArraySerializableInterface;

/**
 * Class UserResetPasswordEntity
 * @ORM\Entity()
 * @ORM\Table(name="user_reset_password")
 * @ORM\HasLifecycleCallbacks()
 * @package Api\User\Entity
 */
class UserResetPasswordEntity extends AbstractEntity implements ArraySerializableInterface
{
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REQUESTED = 'requested';
    public const STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_REQUESTED
    ];

    /**
     * @ORM\ManyToOne(targetEntity="User", cascade={"persist", "remove"}, inversedBy="resetPasswords")
     * @ORM\JoinColumn(name="userUuid", referencedColumnName="uuid", nullable=false)
     * @var User $user
     */
    protected $user;

    /**
     * @ORM\Column(name="expires", type="datetime_immutable", nullable=false)
     * @var DateTimeImmutable
     */
    protected $expires;

    /**
     * @ORM\Column(name="hash", type="string", length=64, nullable=false, unique=true)
     * @var $hash
     */
    protected $hash;

    /**
     * @ORM\Column(name="status", type="string", length=20, nullable=false)
     * @var string $status
     */
    protected $status = self::STATUS_REQUESTED;

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
    public function setUser(User $user)
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
    public function setExpires(DateTimeImmutable $expires)
    {
        $this->expires = $expires;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param $hash
     * @return $this
     */
    public function setHash($hash)
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
    public function setStatus(string $status)
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
    public function isCompleted()
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
        } catch (Exception $exception) {
        }

        return false;
    }

    /**
     * @return $this
     */
    public function markAsCompleted()
    {
        $this->status = self::STATUS_COMPLETED;

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
            'expires' => $this->getExpires(),
            'hash' => $this->getHash(),
            'status' => $this->getStatus(),
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated()
        ];
    }
}
