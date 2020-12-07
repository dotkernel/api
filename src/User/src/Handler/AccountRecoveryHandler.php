<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\User\Form\InputFilter\RecoverIdentityInputFilter;
use Dot\AnnotatedServices\Annotation\Inject;
use Api\App\Common\Message;
use Api\App\RestDispatchTrait;
use Api\User\Entity\User;
use Api\User\Service\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;

/**
 * Class AccountRecoveryHandler
 * @package Api\User\Handler
 */
class AccountRecoveryHandler implements RequestHandlerInterface
{
    use RestDispatchTrait;

    /** @var UserService $userService */
    protected $userService;

    /**
     * AccountRecoveryHandler constructor.
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
        $inputFilter = (new RecoverIdentityInputFilter())->getInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        $user = $this->userService->getUserByEmail($inputFilter->getValue('email'));

        if ($user instanceof User) {
            $this->userService->sendRecoverIdentityMail($user);
        }

        return $this->infoResponse(Message::MAIL_SENT_RECOVER_IDENTITY);
    }
}
