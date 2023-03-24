<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Handler\ResponseTrait;
use Api\App\Message;
use Api\User\Entity\User;
use Api\User\Entity\UserResetPasswordEntity;
use Api\User\InputFilter\ResetPasswordInputFilter;
use Api\User\InputFilter\UpdatePasswordInputFilter;
use Api\User\Service\UserServiceInterface;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class AccountResetPasswordHandler implements RequestHandlerInterface
{
    use ResponseTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     UserServiceInterface::class
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected UserServiceInterface $userService
    ) {}

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $hash = $request->getAttribute('hash');
        $user = $this->userService->findByResetPasswordHash($hash);
        if (!$user instanceof User) {
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

    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $hash = $request->getAttribute('hash');
        $user = $this->userService->findByResetPasswordHash($hash);
        if (!$user instanceof User) {
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

        $inputFilter = (new UpdatePasswordInputFilter())->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $this->userService->updateUser(
                $resetPassword->markAsCompleted()->getUser(),
                $inputFilter->getValues()
            );

            $this->userService->sendResetPasswordCompletedMail($user);

            return $this->infoResponse(Message::RESET_PASSWORD_OK);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new ResetPasswordInputFilter())->setData($request->getParsedBody());
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

            if (!$user instanceof User) {
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
