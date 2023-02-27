<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Message;
use Api\App\Handler\DefaultHandler;
use Api\User\Entity\User;
use Api\User\InputFilter\CreateUserInputFilter;
use Api\User\InputFilter\UpdateUserInputFilter;
use Api\User\Service\UserService;
use Dot\AnnotatedServices\Annotation\Inject;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

use function is_null;
use function sprintf;

/**
 * Class UserHandler
 * @package Api\User\Handler
 */
class UserHandler extends DefaultHandler
{
    protected UserService $userService;

    /**
     * UserHandler constructor.
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
     * Delete user by uuid
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $uuid = $request->getAttribute('uuid');
            $user = $this->userService->findOneBy(['uuid' => $uuid]);
            if (!($user instanceof User)) {
                return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid));
            }
            $user = $this->userService->deleteUser($user);
            return $this->createResponse($request, $user);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    /**
     * If uuid is available: view user by uuid
     * Else: view list of users
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $uuid = $request->getAttribute('uuid');
            if (!is_null($uuid)) {
                $user = $this->userService->findOneBy(['uuid' => $uuid]);
                if (!($user instanceof User)) {
                    return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid));
                }
                return $this->createResponse($request, $user);
            } else {
                return $this->createResponse($request, $this->userService->getUsers($request->getQueryParams()));
            }
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    /**
     * Update user by uuid
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = new UpdateUserInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $uuid = $request->getAttribute('uuid');
            $user = $this->userService->findOneBy(['uuid' => $uuid]);
            if (!($user instanceof User)) {
                return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid));
            }
            $user = $this->userService->updateUser($user, $inputFilter->getValues());
            return $this->createResponse($request, $user);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    /**
     * Create user
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = new CreateUserInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $user = $this->userService->createUser($inputFilter->getValues());
            if ($user->getStatus() === User::STATUS_PENDING) {
                $this->userService->sendActivationMail($user);
            } else {
                $this->userService->sendWelcomeMail($user);
            }
            return $this->createResponse($request, $user);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }
}
