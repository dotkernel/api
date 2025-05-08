<?php

declare(strict_types=1);

namespace Core\User\EventListener;

use Core\User\Entity\User;
use Core\User\Entity\UserAvatar;
use Dot\DependencyInjection\Attribute\Inject;

use function assert;
use function rtrim;
use function sprintf;

class UserAvatarEventListener
{
    /**
     * @param array<non-empty-string, mixed> $config
     */
    #[Inject(
        'config',
    )]
    public function __construct(
        protected array $config = [],
    ) {
    }

    public function postLoad(UserAvatar $avatar): void
    {
        $this->setAvatarUrl($avatar);
    }

    public function postPersist(UserAvatar $avatar): void
    {
        $this->setAvatarUrl($avatar);
    }

    public function postUpdate(UserAvatar $avatar): void
    {
        $this->setAvatarUrl($avatar);
    }

    private function setAvatarUrl(UserAvatar $avatar): void
    {
        assert($avatar->getUser() instanceof User);

        $avatar->setUrl(
            sprintf(
                '%s/%s/%s',
                rtrim($this->config['uploads']['user']['url'], '/'),
                $avatar->getUser()->getUuid()->toString(),
                $avatar->getName()
            )
        );
    }
}
