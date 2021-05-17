<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Message;
use Api\App\Handler\DefaultHandler;
use Api\User\Entity\User;
use Api\User\Entity\UserResetPasswordEntity;
use Api\User\Form\InputFilter\ResetPasswordInputFilter;
use Api\User\Form\InputFilter\UpdatePasswordInputFilter;
use Api\User\Service\UserService;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use function sprintf;

/**
 * Class AccountResetPasswordHandler
 * @package Api\User\Handler
 */
class AccountResetPasswordHandler extends DefaultHandler
{
    protected UserService $userService;

    /**
     * AccountResetPasswordHandler constructor.
     * @param HalResponseFactory $halResponseFactory
     * @param ResourceGenerator $resourceGenerator
     * @param UserService $userService
     *
     * @Inject({HalResponseFactory::class, ResourceGenerator::class, UserService::class})
     */
    public function __construct(
        HalResponseFactory $halResponseFactory,
        ResourceGenerator $resourceGenerator,
        UserService $userService
    ) {
        parent::__construct($halResponseFactory, $resourceGenerator);

        $this->userService = $userService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $hash = $request->getAttribute('hash') ?? null;
        $user = $this->userService->findByResetPasswordHash($hash);
        if (!($user instanceof User)) {
            return $this->notFoundResponse(
                sprintf(Message::RESET_PASSWORD_NOT_FOUND, $hash)
            );
        }

        /** @var UserResetPasswordEntity $resetPassword */
        $resetPassword = $user->getResetPasswords()->filter(
            function (UserResetPasswordEntity $resetPassword) use ($hash) {
                return $resetPassword->getHash() == $hash;
            }
        )->current();
        if (!$resetPassword->isValid()) {
            return $this->errorResponse(sprintf(Message::RESET_PASSWORD_EXPIRED, $hash));
        }
        if ($resetPassword->isCompleted()) {
            return $this->errorResponse(sprintf(Message::RESET_PASSWORD_USED, $hash));
        }

        return $this->infoResponse(sprintf(Message::RESET_PASSWORD_VALID, $hash));
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $hash = $request->getAttribute('hash') ?? null;
        $user = $this->userService->findByResetPasswordHash($hash);
        if (!($user instanceof User)) {
            return $this->notFoundResponse(
                sprintf(Message::RESET_PASSWORD_NOT_FOUND, $hash)
            );
        }

        /** @var UserResetPasswordEntity $resetPassword */
        $resetPassword = $user->getResetPasswords()->filter(
            function (UserResetPasswordEntity $resetPassword) use ($hash) {
                return $resetPassword->getHash() == $hash;
            }
        )->current();
        if (!$resetPassword->isValid()) {
            return $this->errorResponse(sprintf(Message::RESET_PASSWORD_EXPIRED, $hash));
        }
        if ($resetPassword->isCompleted()) {
            return $this->errorResponse(sprintf(Message::RESET_PASSWORD_USED, $hash));
        }

        $inputFilter = (new UpdatePasswordInputFilter())->getInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $this->userService->updateUser($resetPassword->markAsCompleted()->getUser(), $inputFilter->getValues());
            $this->userService->sendResetPasswordCompletedMail($user);
            return $this->infoResponse(Message::RESET_PASSWORD_OK);
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
        $inputFilter = (new ResetPasswordInputFilter())->getInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            if (!empty($inputFilter->getValue('email'))) {
                $user = $this->userService->findByEmail($inputFilter->getValue('email'));
            } elseif (!empty($inputFilter->getValue('identity'))) {
                $user = $this->userService->findByIdentity($inputFilter->getValue('identity'));
            } else {
                $user = null;
            }
            if (!($user instanceof User)) {
                return $this->infoResponse(Message::MAIL_SENT_RESET_PASSWORD);
            }
            $user = $this->userService->updateUser($user->createResetPassword());
            $this->userService->sendResetPasswordRequestedMail($user);
            return $this->infoResponse(Message::MAIL_SENT_RESET_PASSWORD);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }
}
