<?php

declare(strict_types=1);

namespace Api\User\EventListener;

use Api\User\Entity\UserAvatar;
use Doctrine\ORM\Event\LifecycleEventArgs;
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
    /** @var array $config */
    protected $config;

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
    public function postLoad(UserAvatar $avatar)
    {
        $this->setAvatarUrl($avatar);
    }

    /**
     * @param UserAvatar $avatar
     */
    public function postPersist(UserAvatar $avatar)
    {
        $this->setAvatarUrl($avatar);
    }

    /**
     * @param UserAvatar $avatar
     */
    public function postUpdate(UserAvatar $avatar)
    {
        $this->setAvatarUrl($avatar);
    }

    /**
     * @param UserAvatar $avatar
     */
    private function setAvatarUrl(UserAvatar $avatar)
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