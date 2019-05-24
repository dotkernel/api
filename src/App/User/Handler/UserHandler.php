<?php

declare(strict_types=1);

namespace App\User\Handler;

use App\User\Entity\UserRoleEntity;
use App\User\Form\UserUpdateInputFilter;
use Exception;
use App\RestDispatchTrait;
use App\User\Entity\UserEntity;
use App\User\Form\UserCreateInputFilter;
use App\User\Service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;

use function is_null;
use function sprintf;

/**
 * Class UserHandler
 * @package App\User\Handler
 */
class UserHandler implements RequestHandlerInterface
{
    use RestDispatchTrait;

    /** @var UserService $userService */
    protected $userService;

    /** @var UserCreateInputFilter $userCreateInputFilter */
    protected $userCreateInputFilter;

    /** @var UserUpdateInputFilter $userUpdateInputFilter */
    protected $userUpdateInputFilter;

    /**
     * UserHandler constructor.
     * @param HalResponseFactory $halResponseFactory
     * @param ResourceGenerator $resourceGenerator
     * @param UserService $userService
     * @param UserCreateInputFilter $userCreateInputFilter
     * @param UserUpdateInputFilter $userUpdateInputFilter
     */
    public function __construct(
        HalResponseFactory $halResponseFactory,
        ResourceGenerator $resourceGenerator,
        UserService $userService,
        UserCreateInputFilter $userCreateInputFilter,
        UserUpdateInputFilter $userUpdateInputFilter
    ) {
        $this->responseFactory = $halResponseFactory;
        $this->resourceGenerator = $resourceGenerator;
        $this->userService = $userService;
        $this->userCreateInputFilter = $userCreateInputFilter;
        $this->userUpdateInputFilter = $userUpdateInputFilter;
    }

    /**
     * Delete user by uuid
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function delete(ServerRequestInterface $request) : ResponseInterface
    {
        $uuid = $request->getAttribute('uuid', null);
        if (empty($uuid)) {
            return $this->errorResponse('Missing parameter: uuid');
        }

        $user = $this->userService->getUserRepository()->findOneBy(['uuid' => $uuid]);
        if (!($user instanceof UserEntity)) {
            return $this->notFoundResponse(
                sprintf('Unable to find user identified by uuid: %s', $uuid)
            );
        }

        try {
            $this->userService->getUserRepository()->deleteUser($user);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->restResponse(null, 204);
    }

    /**
     * If uuid is available: view user by uuid
     * Else: view list of users
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function get(ServerRequestInterface $request) : ResponseInterface
    {
        $uuid = $request->getAttribute('uuid', null);

        if (!is_null($uuid)) {
            $user = $this->userService->getUserRepository()->findOneBy([
                'uuid' => $uuid
            ]);

            if (!($user instanceof UserEntity)) {
                return $this->notFoundResponse(
                    sprintf('Unable to find user identified by uuid: %s', $uuid)
                );
            }

            return $this->responseFactory->createResponse(
                $request,
                $this->resourceGenerator->fromObject($user, $request)
            );
        } else {
            $users = $this->userService->getUserRepository()->getUsers($request->getQueryParams());

            return $this->responseFactory->createResponse(
                $request,
                $this->resourceGenerator->fromObject($users, $request)
            );
        }
    }

    /**
     * Update user by uuid
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function patch(ServerRequestInterface $request) : ResponseInterface
    {
        $uuid = $request->getAttribute('uuid', null);
        if (empty($uuid)) {
            return $this->errorResponse('Missing parameter: uuid');
        }

        $this->userUpdateInputFilter->setData($request->getParsedBody());
        if (!$this->userUpdateInputFilter->isValid()) {
            return $this->errorResponse($this->userUpdateInputFilter->getMessages());
        }

        $user = $this->userService->getUserRepository()->findOneBy(['uuid' => $uuid]);
        if (!($user instanceof UserEntity)) {
            return $this->notFoundResponse(
                sprintf('Unable to find user identified by uuid: %s', $uuid)
            );
        }

        try {
            $user = $this->userService->updateUser($user, $this->userUpdateInputFilter->getValues());
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->responseFactory->createResponse(
            $request,
            $this->resourceGenerator->fromObject($user, $request)
        );
    }

    /**
     * Create user
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function post(ServerRequestInterface $request) : ResponseInterface
    {
        $this->userCreateInputFilter->setData($request->getParsedBody());

        if (!$this->userCreateInputFilter->isValid()) {
            return $this->errorResponse($this->userCreateInputFilter->getMessages());
        }

        try {
            $user = $this->userService->createUser(
                $this->userCreateInputFilter->getValues(),
                $this->userService->getRole(UserRoleEntity::ROLE_MEMBER)
            );
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->responseFactory->createResponse(
            $request,
            $this->resourceGenerator->fromObject($user, $request)
        );
    }
}
