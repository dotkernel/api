<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Entity\AbstractEntity;
use Api\App\Entity\TimestampsTrait;
use Api\User\Repository\UserDetailRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserDetailRepository::class)]
#[ORM\Table(name: "user_detail")]
#[ORM\HasLifecycleCallbacks]
class UserDetail extends AbstractEntity
{
    use TimestampsTrait;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: "detail")]
    #[ORM\JoinColumn(name: "userUuid", referencedColumnName: "uuid")]
    protected User $user;

    #[ORM\Column(name: "firstName", type: "string", length: 191, nullable: true)]
    protected ?string $firstName = null;

    #[ORM\Column(name: "lastName", type: "string", length: 191, nullable: true)]
    protected ?string $lastName = null;

    #[ORM\Column(name: "email", type: "string", length: 191)]
    protected string $email;

    public function __construct()
    {
        parent::__construct();

        $this->created();
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getArrayCopy(): array
    {
        return [
            'uuid'      => $this->getUuid()->toString(),
            'firstName' => $this->getFirstName(),
            'lastName'  => $this->getLastName(),
            'email'     => $this->getEmail(),
            'created'   => $this->getCreated(),
            'updated'   => $this->getUpdated(),
        ];
    }
}
