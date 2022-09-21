<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Entity\AbstractEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserDetail
 * @ORM\Entity(repositoryClass="Api\User\Repository\UserDetailRepository")
 * @ORM\Table(name="user_detail")
 * @ORM\HasLifecycleCallbacks()
 * @package Api\User\Entity
 */
class UserDetail extends AbstractEntity
{
    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="detail")
     * @ORM\JoinColumn(name="userUuid", referencedColumnName="uuid")
     * @var User $user
     */
    protected User $user;

    /**
     * @ORM\Column(name="firstName", type="string", length=191, nullable=true)
     * @var string|null $firstName
     */
    protected ?string $firstName = null;

    /**
     * @ORM\Column(name="lastName", type="string", length=191, nullable=true)
     * @var string|null $lastName
     */
    protected ?string $lastName = null;

    /**
     * @ORM\Column(name="email", type="string", length=191)
     * @var string $email
     */
    protected string $email;

    /**
     * UserDetail constructor.
     */
    public function __construct()
    {
        parent::__construct();
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
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param $firstName
     * @return $this
     */
    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param $lastName
     * @return $this
     */
    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param $email
     * @return $this
     */
    public function setEmail($email): self
    {
        $this->email = $email;

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
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'email' => $this->getEmail(),
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated()
        ];
    }
}
