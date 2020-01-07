<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\RestDispatchTrait;
use Api\User\Entity\UserEntity;
use Api\User\Form\InputFilter\CreateAccountInputFilter;
use Api\User\Form\InputFilter\UpdateAccountInputFilter;
use Api\User\Service\UserService;
use Dot\AnnotatedServices\Annotation\Inject;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;

/**
 * Class AccountHandler
 * @package Api\User\Handler
 */
class AccountHandler implements RequestHandlerInterface
{
    use RestDispatchTrait;

    /** @var UserService $userService */
    protected $userService;

    /**
     * AccountHandler constructor.
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
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function delete(ServerRequestInterface $request) : ResponseInterface
    {
        $user = $request->getAttribute(UserEntity::class, null);

        try {
            $user = $this->userService->deleteUser($user);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->responseFactory->createResponse($request, $this->resourceGenerator->fromObject($user, $request));
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function get(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->responseFactory->createResponse(
            $request,
            $this->resourceGenerator->fromObject($request->getAttribute(UserEntity::class, null), $request)
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function patch(ServerRequestInterface $request) : ResponseInterface
    {
        $inputFilter = (new UpdateAccountInputFilter())->getInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $user = $this->userService->updateUser(
                $request->getAttribute(UserEntity::class, null),
                $inputFilter->getValues()
            );
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->responseFactory->createResponse($request, $this->resourceGenerator->fromObject($user, $request));
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function post(ServerRequestInterface $request) : ResponseInterface
    {
        $inputFilter = (new CreateAccountInputFilter())->getInputFilter();
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
            $this->userService->sendActivationMail($user);
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->responseFactory->createResponse($request, $this->resourceGenerator->fromObject($user, $request));
    }
}
