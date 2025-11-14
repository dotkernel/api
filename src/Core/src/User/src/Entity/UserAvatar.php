<?php

declare(strict_types=1);

namespace Core\User\Entity;

use Core\App\Entity\AbstractEntity;
use Core\App\Entity\TimestampsTrait;
use Core\App\Entity\UuidIdentifierTrait;
use Core\User\EventListener\UserAvatarEventListener;
use Core\User\Repository\UserAvatarRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @phpstan-type UserAvatarType array{
 *      id: non-empty-string,
 *      url: non-empty-string|null,
 *      created: DateTimeImmutable,
 *      updated: DateTimeImmutable|null
 *  }
 */
#[ORM\Entity(repositoryClass: UserAvatarRepository::class)]
#[ORM\Table(name: 'user_avatar')]
#[ORM\HasLifecycleCallbacks]
#[ORM\EntityListeners([UserAvatarEventListener::class])]
class UserAvatar extends AbstractEntity
{
    use TimestampsTrait;
    use UuidIdentifierTrait;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'avatar')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    protected ?User $user = null;

    #[ORM\Column(name: 'name', type: 'string', length: 191)]
    protected ?string $name = null;

    /** @var non-empty-string|null $url */
    protected ?string $url = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param non-empty-string $url
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return UserAvatarType
     */
    public function getArrayCopy(): array
    {
        return [
            'id'      => $this->id->toString(),
            'url'     => $this->url,
            'created' => $this->created,
            'updated' => $this->updated,
        ];
    }
}
