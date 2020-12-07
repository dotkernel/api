<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Common\Message;
use Api\App\RestDispatchTrait;
use Api\User\Entity\User;
use Api\User\Form\InputFilter\ActivateAccountInputFilter;
use Api\User\Service\UserService;
use Dot\AnnotatedServices\Annotation\Inject;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Mezzio\Helper\UrlHelper;

use function sprintf;

/**
 * Class ActivateAccountInputFilter
 * @package Api\User\Handler
 */
class AccountActivateHandler implements RequestHandlerInterface
{
    use RestDispatchTrait;

    /** @var UrlHelper $urlHelper */
    protected $urlHelper;

    /** @var UserService $userService */
    protected $userService;

    /**
     * AccountActivateHandler constructor.
     * @param HalResponseFactory $halResponseFactory
     * @param ResourceGenerator $resourceGenerator
     * @param UserService $userService
     * @param UrlHelper $urlHelper
     *
     * @Inject({HalResponseFactory::class, ResourceGenerator::class, UserService::class, UrlHelper::class})
     */
    public function __construct(
        HalResponseFactory $halResponseFactory,
        ResourceGenerator $resourceGenerator,
        UserService $userService,
        UrlHelper $urlHelper
    ) {
        $this->responseFactory = $halResponseFactory;
        $this->resourceGenerator = $resourceGenerator;
        $this->userService = $userService;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $hash = $request->getAttribute('hash', null);
        if (empty($hash)) {
            return $this->errorResponse(sprintf(Message::MISSING_PARAMETER, 'hash'));
        }

        $user = $this->userService->findOneBy(['hash' => $hash]);
        if (!($user instanceof User)) {
            return $this->errorResponse(Message::INVALID_ACTIVATION_CODE);
        }

        if ($user->getStatus() === User::STATUS_ACTIVE) {
            return $this->errorResponse(Message::USER_ALREADY_ACTIVATED);
        }

        try {
            $user = $this->userService->activateUser($user);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

//      return $this->responseFactory->createResponse($request, $this->resourceGenerator->fromObject($user, $request));
        return new RedirectResponse($this->urlHelper->generate('home'));
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new ActivateAccountInputFilter())->getInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        $user = $this->userService->findOneBy($inputFilter->getValues());
        if (!($user instanceof User)) {
            return $this->notFoundResponse(
                sprintf(Message::USER_NOT_FOUND_BY_EMAIL, $inputFilter->getValue('email'))
            );
        }

        if ($user->getStatus() === User::STATUS_ACTIVE) {
            return $this->errorResponse(Message::USER_ALREADY_ACTIVATED);
        }

        try {
            $user = $this->userService->updateUser($user->renewHash());
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        try {
            $this->userService->sendActivationMail($user);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->infoResponse(sprintf(Message::MAIL_SENT_USER_ACTIVATION, $user->getEmail()));
    }
}
