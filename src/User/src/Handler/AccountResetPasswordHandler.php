<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\ExpiredException;
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

use function sprintf;

class AccountResetPasswordHandler implements RequestHandlerInterface
{
    use ResponseTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     UserServiceInterface::class,
     *     "config"
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected UserServiceInterface $userService,
        protected array $config,
    ) {
    }

    /**
     * @throws ExpiredException
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
            throw new ExpiredException(sprintf(Message::RESET_PASSWORD_EXPIRED, $hash));
        }
        if ($userResetPassword->isCompleted()) {
            throw new ExpiredException(sprintf(Message::RESET_PASSWORD_USED, $hash));
        }

        return $this->infoResponse(sprintf(Message::RESET_PASSWORD_VALID, $hash));
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws ExpiredException
     * @throws MailException
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
            throw new ExpiredException(sprintf(Message::RESET_PASSWORD_EXPIRED, $hash));
        }
        if ($userResetPassword->isCompleted()) {
            throw new ConflictException(sprintf(Message::RESET_PASSWORD_USED, $hash));
        }

        $inputFilter = (new UpdatePasswordInputFilter())->setData($request->getParsedBody());
        if (! $inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($inputFilter->getMessages());
        }

        $this->userService->updateUser(
            $userResetPassword->markAsCompleted()->getUser(),
            $inputFilter->getValues()
        );

        $this->userService->sendResetPasswordCompletedMail($userResetPassword->getUser());

        return $this->infoResponse(Message::RESET_PASSWORD_OK);
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws MailException
     * @throws NotFoundException
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new ResetPasswordInputFilter())->setData($request->getParsedBody());
        if (! $inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($inputFilter->getMessages());
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
