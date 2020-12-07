<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Common\Message;
use Api\App\RestDispatchTrait;
use Api\User\Entity\User;
use Api\User\Form\InputFilter\UpdateUserInputFilter;
use Api\User\Service\UserService;
use Dot\AnnotatedServices\Annotation\Inject;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;

use function sprintf;

/**
 * Class UserAvatarHandler
 * @package Api\User\Handler
 */
class UserAvatarHandler implements RequestHandlerInterface
{
    use RestDispatchTrait;

    /** @var UserService $userService */
    protected $userService;

    /**
     * UserAvatarHandler constructor.
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
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $uuid = $request->getAttribute('uuid', null);
        if (empty($uuid)) {
            return $this->errorResponse(sprintf(Message::MISSING_PARAMETER, 'uuid'));
        }

        $inputFilter = (new UpdateUserInputFilter())->getInputFilter();
        $inputFilter->setData($request->getUploadedFiles());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        $user = $this->userService->findOneBy(['uuid' => $uuid]);
        if (!($user instanceof User)) {
            return $this->notFoundResponse(
                sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid)
            );
        }

        try {
            $user = $this->userService->updateUser($user, $inputFilter->getValues());
        } catch (Exception $exception) {
            return $this->errorResponse($exception->getMessage());
        }

        return $this->responseFactory->createResponse($request, $this->resourceGenerator->fromObject($user, $request));
    }
}
