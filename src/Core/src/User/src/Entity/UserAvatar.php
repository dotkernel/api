<?php

declare(strict_types=1);

namespace Core\User\Entity;

use Core\App\Entity\AbstractEntity;
use Core\App\Entity\TimestampsTrait;
use Core\User\EventListener\UserAvatarEventListener;
use Core\User\Repository\UserAvatarRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserAvatarRepository::class)]
#[ORM\Table(name: 'user_avatar')]
#[ORM\HasLifecycleCallbacks]
#[ORM\EntityListeners([UserAvatarEventListener::class])]
class UserAvatar extends AbstractEntity
{
    use TimestampsTrait;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'avatar')]
    #[ORM\JoinColumn(name: 'userUuid', referencedColumnName: 'uuid')]
    protected ?User $user = null;

    #[ORM\Column(name: 'name', type: 'string', length: 191)]
    protected ?string $name = null;

    protected ?string $url = null;

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

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getArrayCopy(): array
    {
        return [
            'uuid'    => $this->uuid->toString(),
            'url'     => $this->url,
            'created' => $this->created,
            'updated' => $this->updated,
        ];
    }
}
