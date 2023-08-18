<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Handler\ResponseTrait;
use Api\User\Entity\User;
use Api\User\InputFilter\CreateUserInputFilter;
use Api\User\InputFilter\UpdateUserInputFilter;
use Api\User\Service\UserServiceInterface;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class AccountHandler implements RequestHandlerInterface
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

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $user = $this->userService->deleteUser($request->getAttribute(User::class));

            return $this->createResponse($request, $user);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse($request, $request->getAttribute(User::class));
    }

    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new UpdateUserInputFilter())
            ->setValidationGroup(['password', 'passwordConfirm', 'detail'])
            ->setData($request->getParsedBody());
        if (! $inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $user = $this->userService->updateUser($request->getAttribute(User::class), $inputFilter->getValues());

            return $this->createResponse($request, $user);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new CreateUserInputFilter())
            ->setValidationGroup(['identity', 'password', 'passwordConfirm', 'detail'])
            ->setData($request->getParsedBody());
        if (! $inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $user = $this->userService->createUser($inputFilter->getValues());

            $this->userService->sendActivationMail($user);

            return $this->createResponse($request, $user);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }
}
