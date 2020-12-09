<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Common\Message;
use Api\App\RestDispatchTrait;
use Api\User\Entity\User;
use Api\User\Form\InputFilter\CreateUserInputFilter;
use Api\User\Form\InputFilter\UpdateUserInputFilter;
use Api\User\Service\UserService;
use Dot\AnnotatedServices\Annotation\Inject;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;

use function is_null;
use function sprintf;

/**
 * Class UserHandler
 * @package Api\User\Handler
 */
class UserHandler implements RequestHandlerInterface
{
    use RestDispatchTrait;

    /** @var UserService $userService */
    protected $userService;

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
        $this->responseFactory = $halResponseFactory;
        $this->resourceGenerator = $resourceGenerator;
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
        $uuid = $request->getAttribute('uuid', null);
        if (empty($uuid)) {
            return $this->errorResponse(sprintf(Message::MISSING_PARAMETER, 'uuid'));
        }

        $inputFilter = (new UpdateUserInputFilter())->getInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        $user = $this->userService->findOneBy(['uuid' => $uuid]);
        if (!($user instanceof User)) {
            return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid));
        }

        try {
            $this->userService->deleteUser($user);
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
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $uuid = $request->getAttribute('uuid', null);

        if (!is_null($uuid)) {
            $user = $this->userService->findOneBy(['uuid' => $uuid]);
            if (!($user instanceof User)) {
                return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid));
            }

            return $this->responseFactory
                ->createResponse($request, $this->resourceGenerator->fromObject($user, $request));
        } else {
            try {
                return $this->responseFactory->createResponse(
                    $request,
                    $this->resourceGenerator
                        ->fromObject($this->userService->getUsers($request->getQueryParams()), $request)
                );
            } catch (Exception $exception) {
                return $this->errorResponse($exception->getMessage());
            }
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
        $uuid = $request->getAttribute('uuid', null);
        if (empty($uuid)) {
            return $this->errorResponse(sprintf(Message::MISSING_PARAMETER, 'uuid'));
        }

        $inputFilter = (new UpdateUserInputFilter())->getInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        $user = $this->userService->findOneBy(['uuid' => $uuid]);
        if (!($user instanceof User)) {
            return $this->notFoundResponse(sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid));
        }

        try {
            $user = $this->userService->updateUser($user, $inputFilter->getValues());
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->responseFactory->createResponse($request, $this->resourceGenerator->fromObject($user, $request));
    }

    /**
     * Create user
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new CreateUserInputFilter())->getInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $user = $this->userService->createUser($inputFilter->getValues());
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        try {
            if ($user->getStatus() === User::STATUS_PENDING) {
                $this->userService->sendActivationMail($user);
            } else {
                $this->userService->sendWelcomeMail($user);
            }
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->responseFactory->createResponse($request, $this->resourceGenerator->fromObject($user, $request));
    }
}
