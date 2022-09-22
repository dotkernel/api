<?php

declare(strict_types=1);

namespace Api\User\EventListener;

use Api\User\Entity\UserAvatar;
use Dot\AnnotatedServices\Annotation\Inject;
use Dot\AnnotatedServices\Annotation\Service;

/**
 * Class UserAvatarEventListener
 * @package Api\User\EventListener
 *
 * @Service
 */
class UserAvatarEventListener
{
    protected ?array $config;

    /**
     * UserAvatarEventListener constructor.
     * @param array $config
     *
     * @Inject({"config"})
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @param UserAvatar $avatar
     */
    public function postLoad(UserAvatar $avatar): void
    {
        $this->setAvatarUrl($avatar);
    }

    /**
     * @param UserAvatar $avatar
     */
    public function postPersist(UserAvatar $avatar): void
    {
        $this->setAvatarUrl($avatar);
    }

    /**
     * @param UserAvatar $avatar
     */
    public function postUpdate(UserAvatar $avatar): void
    {
        $this->setAvatarUrl($avatar);
    }

    /**
     * @param UserAvatar $avatar
     */
    private function setAvatarUrl(UserAvatar $avatar): void
    {
        $avatar->setUrl(
            sprintf(
                '%s/%s/%s',
                $this->config['uploads']['user']['url'],
                $avatar->getUser()->getUuid()->toString(),
                $avatar->getName()
            )
        );
    }
}
