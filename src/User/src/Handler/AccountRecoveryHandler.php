<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Handler\ResponseTrait;
use Api\App\Message;
use Api\User\Entity\User;
use Api\User\InputFilter\RecoverIdentityInputFilter;
use Api\User\Service\UserServiceInterface;
use Dot\AnnotatedServices\Annotation\Inject;
use Exception;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

use function sprintf;

class AccountRecoveryHandler implements RequestHandlerInterface
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

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new RecoverIdentityInputFilter())->setData($request->getParsedBody());
        if (! $inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $email = $inputFilter->getValue('email');

            $user = $this->userService->findByEmail($email);
            if (! $user instanceof User) {
                throw new Exception(
                    sprintf(Message::USER_NOT_FOUND_BY_EMAIL, $email)
                );
            }

            $this->userService->sendRecoverIdentityMail($user);

            return $this->infoResponse(Message::MAIL_SENT_RECOVER_IDENTITY);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }
}
