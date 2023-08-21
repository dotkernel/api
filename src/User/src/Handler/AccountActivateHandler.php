<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Handler\ResponseTrait;
use Api\App\Message;
use Api\User\Entity\User;
use Api\User\InputFilter\ActivateAccountInputFilter;
use Api\User\Service\UserServiceInterface;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Mezzio\Helper\UrlHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function sprintf;

class AccountActivateHandler implements RequestHandlerInterface
{
    use ResponseTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     UserServiceInterface::class,
     *     UrlHelper::class
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected UserServiceInterface $userService,
        protected UrlHelper $urlHelper
    ) {
    }

    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $hash = $request->getAttribute('hash');
        $user = $this->userService->findOneBy(['hash' => $hash]);
        if (! $user instanceof User) {
            return $this->errorResponse(Message::INVALID_ACTIVATION_CODE);
        }

        if ($user->isActive()) {
            return $this->errorResponse(Message::USER_ALREADY_ACTIVATED);
        }

        try {
            $this->userService->activateUser($user);

            return $this->infoResponse(Message::USER_ACTIVATED);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new ActivateAccountInputFilter())->setData($request->getParsedBody());
        if (! $inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        $email = $inputFilter->getValue('email');
        $user  = $this->userService->findByEmail($email);
        if (! $user instanceof User) {
            return $this->notFoundResponse(
                sprintf(Message::USER_NOT_FOUND_BY_EMAIL, $email)
            );
        }

        if ($user->isActive()) {
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
