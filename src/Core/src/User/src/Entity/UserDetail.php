<?php

declare(strict_types=1);

namespace Core\User\Entity;

use Core\App\Entity\AbstractEntity;
use Core\App\Entity\TimestampsTrait;
use Core\User\Repository\UserDetailRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @phpstan-type UserDetailType array{
 *      uuid: non-empty-string,
 *      firstName: non-empty-string|null,
 *      lastName: non-empty-string|null,
 *      email: non-empty-string|null,
 *      created: DateTimeImmutable,
 *      updated: DateTimeImmutable|null,
 *  }
 */
#[ORM\Entity(repositoryClass: UserDetailRepository::class)]
#[ORM\Table(name: 'user_detail')]
#[ORM\HasLifecycleCallbacks]
class UserDetail extends AbstractEntity
{
    use TimestampsTrait;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'detail')]
    #[ORM\JoinColumn(name: 'userUuid', referencedColumnName: 'uuid')]
    protected ?User $user = null;

    /** @var non-empty-string|null $firstName */
    #[ORM\Column(name: 'firstName', type: 'string', length: 191, nullable: true)]
    protected ?string $firstName = null;

    /** @var non-empty-string|null $lastName */
    #[ORM\Column(name: 'lastName', type: 'string', length: 191, nullable: true)]
    protected ?string $lastName = null;

    /** @var non-empty-string|null $email */
    #[ORM\Column(name: 'email', type: 'string', length: 191)]
    protected ?string $email = null;

    public function __construct()
    {
        parent::__construct();

        $this->created();
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param non-empty-string|null $firstName
     */
    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param non-empty-string|null $lastName
     */
    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function hasEmail(): bool
    {
        return $this->email !== null && $this->email !== '';
    }

    /**
     * @param non-empty-string $email
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return UserDetailType
     */
    public function getArrayCopy(): array
    {
        return [
            'uuid'      => $this->uuid->toString(),
            'firstName' => $this->firstName,
            'lastName'  => $this->lastName,
            'email'     => $this->email,
            'created'   => $this->created,
            'updated'   => $this->updated,
        ];
    }
}
