<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Exception\ConflictException;
use Api\App\Exception\FormValidationException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\ResponseTrait;
use Api\App\Message;
use Api\User\Entity\User;
use Api\User\InputFilter\ActivateAccountInputFilter;
use Api\User\Service\UserServiceInterface;
use Dot\AnnotatedServices\Annotation\Inject;
use Dot\Mail\Exception\MailException;
use Fig\Http\Message\StatusCodeInterface;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Mezzio\Helper\UrlHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function sprintf;

class AccountActivateHandler implements RequestHandlerInterface
{
    use ResponseTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     UserServiceInterface::class,
     *     UrlHelper::class,
     *     "config"
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected UserServiceInterface $userService,
        protected UrlHelper $urlHelper,
        protected array $config,
    ) {
    }

    /**
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $hash = $request->getAttribute('hash');

        $user = $this->userService->findOneBy(['hash' => $hash]);
        if (! $user instanceof User) {
            throw new NotFoundException(Message::INVALID_ACTIVATION_CODE);
        }

        if ($user->isActive()) {
            throw new ConflictException(Message::USER_ALREADY_ACTIVATED);
        }

        $this->userService->activateUser($user);

        return $this->infoResponse(Message::USER_ACTIVATED);
    }

    /**
     * @throws ConflictException
     * @throws FormValidationException
     * @throws MailException
     * @throws NotFoundException
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new ActivateAccountInputFilter())->setData($request->getParsedBody());
        if (! $inputFilter->isValid()) {
            throw (new FormValidationException())->setMessages($inputFilter->getMessages());
        }

        $email = $inputFilter->getValue('email');
        $user  = $this->userService->findByEmail($email);
        if (! $user instanceof User) {
            throw new NotFoundException(sprintf(Message::USER_NOT_FOUND_BY_EMAIL, $email));
        }

        if ($user->isActive()) {
            throw new ConflictException(Message::USER_ALREADY_ACTIVATED);
        }

        $user = $this->userService->activateUser($user);
        $this->userService->sendActivationMail($user);

        return $this->infoResponse(
            sprintf(Message::MAIL_SENT_USER_ACTIVATION, $user->getDetail()->getEmail()),
            StatusCodeInterface::STATUS_CREATED
        );
    }
}
