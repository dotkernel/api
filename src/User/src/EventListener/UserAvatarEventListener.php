<?php

declare(strict_types=1);

namespace Api\User\EventListener;

use Api\User\Entity\UserAvatarEntity;
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
     * @param UserAvatarEntity $avatar
     */
    public function postLoad(UserAvatarEntity $avatar)
    {
        $this->setAvatarUrl($avatar);
    }

    /**
     * @param UserAvatarEntity $avatar
     */
    public function postPersist(UserAvatarEntity $avatar)
    {
        $this->setAvatarUrl($avatar);
    }

    /**
     * @param UserAvatarEntity $avatar
     */
    public function postUpdate(UserAvatarEntity $avatar)
    {
        $this->setAvatarUrl($avatar);
    }

    /**
     * @param UserAvatarEntity $avatar
     */
    private function setAvatarUrl(UserAvatarEntity $avatar)
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