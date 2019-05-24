<?php

declare(strict_types=1);

namespace App\User\Handler;

use App\User\Entity\UserEntity;
use Exception;
use App\RestDispatchTrait;
use App\User\Form\UserAvatarInputFilter;
use App\User\Service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Expressive\Hal\HalResponseFactory;
use Zend\Expressive\Hal\ResourceGenerator;
use Zend\Psr7Bridge\Psr7ServerRequest;

/**
 * Class UserAvatarHandler
 * @package App\User\Handler
 */
class UserAvatarHandler implements RequestHandlerInterface
{
    use RestDispatchTrait;

    /** @var UserService $userService */
    protected $userService;

    /** @var UserAvatarInputFilter $userAvatarInputFilter */
    protected $userAvatarInputFilter;

    /**
     * UserAvatarHandler constructor.
     * @param HalResponseFactory $halResponseFactory
     * @param ResourceGenerator $resourceGenerator
     * @param UserService $userService
     * @param UserAvatarInputFilter $userAvatarInputFilter
     */
    public function __construct(
        HalResponseFactory $halResponseFactory,
        ResourceGenerator $resourceGenerator,
        UserService $userService,
        UserAvatarInputFilter $userAvatarInputFilter
    ) {
        $this->responseFactory = $halResponseFactory;
        $this->resourceGenerator = $resourceGenerator;
        $this->userService = $userService;
        $this->userAvatarInputFilter = $userAvatarInputFilter;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function post(ServerRequestInterface $request) : ResponseInterface
    {
        $user = $request->getAttribute(UserEntity::class, null);

        #TODO: check why won't the InpuFilter work with $request->getUploadedFiles()
        $this->userAvatarInputFilter->setData(Psr7ServerRequest::toZend($request)->getFiles());
        if (!$this->userAvatarInputFilter->isValid()) {
            return $this->errorResponse($this->userAvatarInputFilter->getMessages());
        }

        try {
            $user = $this->userService->updateUser($user, $this->userAvatarInputFilter->getValues());
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->responseFactory->createResponse(
            $request,
            $this->resourceGenerator->fromObject($user, $request)
        );
    }
}
