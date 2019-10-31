<?php

declare(strict_types=1);

namespace Api\App\MailChimp\Service;

use Api\App\Common\Message;
use Api\User\Entity\UserEntity;
use Dot\AnnotatedServices\Annotation\Inject;
use DrewM\MailChimp\MailChimp;
use Exception;

use function sprintf;

/**
 * Class MailChimpService
 * @package Api\App\MailChimp\Service
 */
class MailChimpService
{
    const STATUS_CLEANED = 'cleaned';
    const STATUS_PENDING = 'pending';
    const STATUS_SUBSCRIBED = 'subscribed';
    const STATUS_TRANSACTIONAL = 'transactional';
    const STATUS_UNSUBSCRIBED = 'unsubscribed';
    const STATUSES = [
        self::STATUS_CLEANED,
        self::STATUS_PENDING,
        self::STATUS_SUBSCRIBED,
        self::STATUS_TRANSACTIONAL,
        self::STATUS_UNSUBSCRIBED
    ];

    /** @var array $config */
    protected $config;

    /** @var MailChimp $mailChimp */
    protected $mailChimp;

    /**
     * MailChimpService constructor.
     * @param MailChimp $mailChimp
     * @param array $config
     *
     * @Inject({MailChimp::class, "config.mailChimp"})
     */
    public function __construct(MailChimp $mailChimp, array $config = [])
    {
        $this->mailChimp = $mailChimp;
        $this->config = $config;
    }

    /**
     * @param UserEntity $user
     * @param string $listId
     * @return array|false
     */
    public function deleteSubscription(UserEntity $user, string $listId)
    {
        return $this->mailChimp->delete(
            sprintf('/lists/%s/members/%s', $listId, MailChimp::subscriberHash($user->getEmail()))
        );
    }

    /**
     * @param UserEntity $user
     * @param string $listId
     * @param string $status
     * @return array|false
     * @throws Exception
     */
    public function updateSubscription(UserEntity $user, string $listId, string $status)
    {
        if (!in_array($status, self::STATUSES)) {
            throw new Exception(sprintf(Message::INVALID_VALUE, 'status'));
        }

        return $this->mailChimp->put(
            sprintf('/lists/%s/members/%s', $listId, MailChimp::subscriberHash($user->getEmail())),
            ['status' => $status]
        );
    }
}
