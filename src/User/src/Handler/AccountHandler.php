<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Handler\DefaultHandler;
use Dot\AnnotatedServices\Annotation\Inject;
use Api\User\Entity\User;
use Api\User\Form\InputFilter\CreateAccountInputFilter;
use Api\User\Form\InputFilter\UpdateAccountInputFilter;
use Api\User\Service\UserService;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Class AccountHandler
 * @package Api\User\Handler
 */
class AccountHandler extends DefaultHandler
{
    protected UserService $userService;

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
        parent::__construct($halResponseFactory, $resourceGenerator);
        $this->userService = $userService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $user = $this->userService->deleteUser($request->getAttribute(User::class));
            return $this->createResponse($request, $user);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse($request, $request->getAttribute(User::class));
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new UpdateAccountInputFilter())->getInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        try {
            $user = $this->userService->updateUser($request->getAttribute(User::class), $inputFilter->getValues());
            return $this->createResponse($request, $user);
        } catch (Throwable $exception) {
            return $this->errorResponse($exception->getMessage());
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new CreateAccountInputFilter())->getInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
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
