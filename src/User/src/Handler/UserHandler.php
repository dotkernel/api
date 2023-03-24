<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Handler\ResponseTrait;
use Api\App\Message;
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

class UserHandler implements RequestHandlerInterface
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

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $uuid = $request->getAttribute('uuid');
            $user = $this->userService->findOneBy(['uuid' => $uuid]);
            if (!$user instanceof User) {
                return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid));
            }

            $user = $this->userService->deleteUser($user);

            return $this->createResponse($request, $user);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $uuid = $request->getAttribute('uuid');
            if (!empty($uuid)) {
                return $this->view($request, $uuid);
            }

            return $this->list($request);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new UpdateUserInputFilter())->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $uuid = $request->getAttribute('uuid');
            $user = $this->userService->findOneBy(['uuid' => $uuid]);
            if (!$user instanceof User) {
                return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid));
            }

            $user = $this->userService->updateUser($user, $inputFilter->getValues());

            return $this->createResponse($request, $user);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new CreateUserInputFilter())->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $user = $this->userService->createUser($inputFilter->getValues());
            if ($user->isPending()) {
                $this->userService->sendActivationMail($user);
            } elseif ($user->isActive()) {
                $this->userService->sendWelcomeMail($user);
            }

            return $this->createResponse($request, $user);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    private function list(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse($request, $this->userService->getUsers($request->getQueryParams()));
    }

    private function view(ServerRequestInterface $request, string $uuid): ResponseInterface
    {
        $user = $this->userService->findOneBy(['uuid' => $uuid]);
        if (!$user instanceof User) {
            return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid));
        }
        return $this->createResponse($request, $user);
    }
}
