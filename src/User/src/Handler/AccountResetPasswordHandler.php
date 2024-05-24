<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Exception\FormValidationException;
use Api\App\Exception\InvalidResetPasswordException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\ResponseTrait;
use Api\App\Message;
use Api\User\Entity\User;
use Api\User\Entity\UserResetPasswordEntity;
use Api\User\InputFilter\ResetPasswordInputFilter;
use Api\User\InputFilter\UpdatePasswordInputFilter;
use Api\User\Service\UserServiceInterface;
use Dot\AnnotatedServices\Annotation\Inject;
use Dot\Mail\Exception\MailException;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function sprintf;

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
    ) {
    }

    /**
     * @throws InvalidResetPasswordException
     * @throws NotFoundException
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $hash = $request->getAttribute('hash');

        $userResetPassword = $this->userService->findResetPasswordByHash($hash);
        if (! $userResetPassword instanceof UserResetPasswordEntity) {
            throw new NotFoundException(sprintf(Message::RESET_PASSWORD_NOT_FOUND, $hash));
        }

        if (! $userResetPassword->isValid()) {
            throw new InvalidResetPasswordException(sprintf(Message::RESET_PASSWORD_EXPIRED, $hash));
        }
        if ($userResetPassword->isCompleted()) {
            throw new InvalidResetPasswordException(sprintf(Message::RESET_PASSWORD_USED, $hash));
        }

        return $this->infoResponse(sprintf(Message::RESET_PASSWORD_VALID, $hash));
    }

    /**
     * @throws FormValidationException
     * @throws NotFoundException
     */
    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $hash = $request->getAttribute('hash');

        $userResetPassword = $this->userService->findResetPasswordByHash($hash);
        if (! $userResetPassword instanceof UserResetPasswordEntity) {
            throw new NotFoundException(sprintf(Message::RESET_PASSWORD_NOT_FOUND, $hash));
        }

        if (! $userResetPassword->isValid()) {
            return $this->errorResponse(sprintf(Message::RESET_PASSWORD_EXPIRED, $hash));
        }
        if ($userResetPassword->isCompleted()) {
            return $this->errorResponse(sprintf(Message::RESET_PASSWORD_USED, $hash));
        }

        $inputFilter = (new UpdatePasswordInputFilter())->setData($request->getParsedBody());
        if (! $inputFilter->isValid()) {
            throw (new FormValidationException())->setMessages($inputFilter->getMessages());
        }

        try {
            $this->userService->updateUser(
                $userResetPassword->markAsCompleted()->getUser(),
                $inputFilter->getValues()
            );

            $this->userService->sendResetPasswordCompletedMail($userResetPassword->getUser());

            return $this->infoResponse(Message::RESET_PASSWORD_OK);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    /**
     * @throws FormValidationException
     * @throws MailException
     * @throws NotFoundException
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new ResetPasswordInputFilter())->setData($request->getParsedBody());
        if (! $inputFilter->isValid()) {
            throw (new FormValidationException())->setMessages($inputFilter->getMessages());
        }

        if (! empty($inputFilter->getValue('email'))) {
            $user = $this->userService->findByEmail($inputFilter->getValue('email'));
        } elseif (! empty($inputFilter->getValue('identity'))) {
            $user = $this->userService->findByIdentity($inputFilter->getValue('identity'));
        } else {
            $user = null;
        }

        if (! $user instanceof User) {
            throw new NotFoundException(Message::USER_NOT_FOUND);
        }

        $user = $this->userService->updateUser($user->createResetPassword());

        $this->userService->sendResetPasswordRequestedMail($user);

        return $this->infoResponse(Message::MAIL_SENT_RESET_PASSWORD);
    }
}
