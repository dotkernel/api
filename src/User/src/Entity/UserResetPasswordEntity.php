<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Common\Entity\AbstractEntity;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Zend\Stdlib\ArraySerializableInterface;

/**
 * Class UserResetPasswordEntity
 * @ORM\Entity()
 * @ORM\Table(name="user_reset_password")
 * @ORM\HasLifecycleCallbacks()
 * @package Api\User\Entity
 */
class UserResetPasswordEntity extends AbstractEntity implements ArraySerializableInterface
{
    const STATUS_COMPLETED = 'completed';
    const STATUS_REQUESTED = 'requested';
    const STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_REQUESTED
    ];

    /**
     * @ORM\ManyToOne(targetEntity="UserEntity", cascade={"persist", "remove"}, inversedBy="resetPasswords")
     * @ORM\JoinColumn(name="userUuid", referencedColumnName="uuid", nullable=false)
     * @var UserEntity $user
     */
    protected $user;

    /**
     * @ORM\Column(name="expires", type="datetime", nullable=false)
     * @var DateTime
     */
    protected $expires;

    /**
     * @ORM\Column(name="hash", type="string", length=255, unique=true)
     * @var $hash
     */
    protected $hash;

    /**
     * @ORM\Column(name="status", type="string", nullable=false)
     * @var string $status
     */
    protected $status = self::STATUS_REQUESTED;

    /**
     * UserResetPasswordEntity constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->expires = new DateTime();
        $this->expires->add(new \DateInterval('P1D'));
    }

    /**
     * @return UserEntity
     */
    public function getUser(): UserEntity
    {
        return $this->user;
    }

    /**
     * @param UserEntity $user
     * @return $this
     */
    public function setUser(UserEntity $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getExpires(): DateTime
    {
        return $this->expires;
    }

    /**
     * @param DateTime $expires
     * @return $this
     */
    public function setExpires(DateTime $expires)
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
            return $this->getExpires() > (new DateTime());
        } catch (\Exception $exception) {}

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
