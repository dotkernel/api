<?php

declare(strict_types=1);

namespace Api\User\Entity;

use Api\App\Entity\AbstractEntity;
use Api\User\EventListener\UserAvatarEventListener;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class UserAvatar
 * @ORM\Entity(repositoryClass="Api\User\Repository\UserAvatarRepository")
 * @ORM\Table(name="user_avatar")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\EntityListeners({UserAvatarEventListener::class})
 * @package Api\User\Entity
 */
class UserAvatar extends AbstractEntity
{
    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="avatar")
     * @ORM\JoinColumn(name="userUuid", referencedColumnName="uuid")
     * @var User $user
     */
    protected User $user;

    /**
     * @ORM\Column(name="name", type="string", length=191)
     * @var string $name
     */
    protected string $name;

    /** @var string|null $url */
    protected ?string $url;

    /**
     * UserAvatar constructor.
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
    public function setUser(User $user): UserAvatar
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): UserAvatar
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Helper methods
     */

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): UserAvatar
    {
        $this->url = $url;

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
            'url' => $this->getUrl(),
            'created' => $this->getCreated(),
            'updated' => $this->getUpdated()
        ];
    }
}
