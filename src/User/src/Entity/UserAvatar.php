<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Entity\AbstractEntity;
use Api\User\EventListener\UserAvatarEventListener;
use Api\User\Repository\UserAvatarRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserAvatarRepository::class)]
#[ORM\Table(name: "user_avatar")]
#[ORM\HasLifecycleCallbacks]
#[ORM\EntityListeners([UserAvatarEventListener::class])]
class UserAvatar extends AbstractEntity
{
    #[ORM\OneToOne(inversedBy: "avatar", targetEntity: User::class)]
    #[ORM\JoinColumn(name: "userUuid", referencedColumnName: "uuid")]
    protected User $user;

    #[ORM\Column(name: "name", type: "string", length: 191)]
    protected string $name;

    protected ?string $url = null;

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): UserAvatar
    {
        $this->user = $user;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): UserAvatar
    {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): UserAvatar
    {
        $this->url = $url;

        return $this;
    }

    public function getArrayCopy(): array
    {
        return [
            'uuid'    => $this->getUuid()->toString(),
            'url'     => $this->getUrl(),
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated(),
        ];
    }
}
