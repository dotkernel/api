<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Message;
use Api\App\Handler\DefaultHandler;
use Api\User\Entity\User;
use Api\User\Form\InputFilter\ActivateAccountInputFilter;
use Api\User\Service\UserService;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Mezzio\Helper\UrlHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use function sprintf;

/**
 * Class ActivateAccountInputFilter
 * @package Api\User\Handler
 */
class AccountActivateHandler extends DefaultHandler
{
    protected UrlHelper $urlHelper;

    protected UserService $userService;

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
        parent::__construct($halResponseFactory, $resourceGenerator);

        $this->userService = $userService;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $hash = $request->getAttribute('hash');
        $user = $this->userService->findOneBy(['hash' => $hash]);
        if (!($user instanceof User)) {
            return $this->errorResponse(Message::INVALID_ACTIVATION_CODE);
        }

        if ($user->getStatus() === User::STATUS_ACTIVE) {
            return $this->errorResponse(Message::USER_ALREADY_ACTIVATED);
        }

        try {
            $this->userService->activateUser($user);
            return $this->infoResponse(Message::USER_ACTIVATED);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
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

        $email = $inputFilter->getValue('email');
        $user = $this->userService->findByEmail($email);
        if (!($user instanceof User)) {
            return $this->notFoundResponse(
                sprintf(Message::USER_NOT_FOUND_BY_EMAIL, $email)
            );
        }

        if ($user->getStatus() === User::STATUS_ACTIVE) {
            return $this->errorResponse(Message::USER_ALREADY_ACTIVATED);
        }

        try {
            $user = $this->userService->activateUser($user);
            $this->userService->sendActivationMail($user);
            return $this->infoResponse(
                sprintf(Message::MAIL_SENT_USER_ACTIVATION, $user->getDetail()->getEmail())
            );
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }
}
