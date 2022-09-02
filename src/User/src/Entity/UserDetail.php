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
     * @ORM\Column(name="firstname", type="string", length=191, nullable=true)
     * @var string|null $firstname
     */
    protected ?string $firstname;

    /**
     * @ORM\Column(name="lastname", type="string", length=191, nullable=true)
     * @var string|null $lastname
     */
    protected ?string $lastname;

    /**
     * @ORM\Column(name="email", type="string", length=191, nullable=true)
     * @var string|null $email
     */
    protected ?string $email;

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
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    /**
     * @param $firstname
     * @return $this
     */
    public function setFirstname($firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    /**
     * @param $lastname
     * @return $this
     */
    public function setLastname($lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
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
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'email' => $this->getEmail(),
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated()
        ];
    }
}
