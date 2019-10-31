<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Common\Message;
use Api\App\MailChimp\Service\MailChimpService;
use Api\App\RestDispatchTrait;
use Api\User\Entity\UserEntity;
use Api\User\Service\UserService;
use Dot\AnnotatedServices\Annotation\Inject;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;
use Zend\Http\Response;

/**
 * Class UserSubscriptionHandler
 * @package Api\User\Handler
 */
class UserSubscriptionHandler implements RequestHandlerInterface
{
    use RestDispatchTrait;

    /** @var MailChimpService $mailChimpService */
    protected $mailChimpService;

    /** @var UserService $userService */
    protected $userService;

    /**
     * UserSubscriptionHandler constructor.
     * @param HalResponseFactory $halResponseFactory
     * @param ResourceGenerator $resourceGenerator
     * @param UserService $userService
     * @param MailChimpService $mailChimpService
     *
     * @Inject({HalResponseFactory::class, ResourceGenerator::class, UserService::class, MailChimpService::class})
     */
    public function __construct(
        HalResponseFactory $halResponseFactory,
        ResourceGenerator $resourceGenerator,
        UserService $userService,
        MailChimpService $mailChimpService
    ) {
        $this->responseFactory = $halResponseFactory;
        $this->resourceGenerator = $resourceGenerator;
        $this->mailChimpService = $mailChimpService;
        $this->userService = $userService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function delete(ServerRequestInterface $request) : ResponseInterface
    {
        $uuid = $request->getAttribute('uuid', null);
        $list = $request->getAttribute('list', null);

        $user = $this->userService->findOneBy(['uuid' => $uuid]);
        if (!($user instanceof UserEntity)) {
            return $this->notFoundResponse(
                sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid)
            );
        }

        try {
            $this->mailChimpService->deleteSubscription($user, $list);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->restResponse(null, Response::STATUS_CODE_204);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function put(ServerRequestInterface $request) : ResponseInterface
    {
        $uuid = $request->getAttribute('uuid', null);
        $list = $request->getAttribute('list', null);
        $status = $request->getAttribute('status', '');

        $user = $this->userService->findOneBy(['uuid' => $uuid]);
        if (!($user instanceof UserEntity)) {
            return $this->notFoundResponse(
                sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid)
            );
        }

        try {
            $this->mailChimpService->updateSubscription($user, $list, $status);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->infoResponse(sprintf(Message::NEWSLETTER_SUBSCRIPTION_UPDATED, $status));
    }
}
