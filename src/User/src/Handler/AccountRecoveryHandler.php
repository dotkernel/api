<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Handler\DefaultHandler;
use Api\App\Message;
use Api\User\Entity\User;
use Api\User\InputFilter\RecoverIdentityInputFilter;
use Api\User\Service\UserService;
use Dot\AnnotatedServices\Annotation\Inject;
use Dot\Mail\Exception\MailException;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AccountRecoveryHandler
 * @package Api\User\Handler
 */
class AccountRecoveryHandler extends DefaultHandler
{
    protected UserService $userService;

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
        parent::__construct($halResponseFactory, $resourceGenerator);

        $this->userService = $userService;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws MailException
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = new RecoverIdentityInputFilter();
        $inputFilter->setData($request->getParsedBody());
        if (!$inputFilter->isValid()) {
            return $this->errorResponse($inputFilter->getMessages());
        }

        $user = $this->userService->findByEmail($inputFilter->getValue('email'));
        if ($user instanceof User) {
            $this->userService->sendRecoverIdentityMail($user);
        }

        return $this->infoResponse(Message::MAIL_SENT_RECOVER_IDENTITY);
    }
}
