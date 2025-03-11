<?php

declare(strict_types=1);

namespace Api\User\Handler\Account\Avatar;

use Api\App\Exception\BadRequestException;
use Api\App\Handler\AbstractHandler;
use Api\User\InputFilter\UpdateAvatarInputFilter;
use Api\User\Service\UserAvatarServiceInterface;
use Core\User\Entity\User;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostUserAccountAvatarHandler extends AbstractHandler
{
    #[Inject(
        UserAvatarServiceInterface::class,
    )]
    public function __construct(
        protected UserAvatarServiceInterface $userAvatarService,
    ) {
    }

    /**
     * @throws BadRequestException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new UpdateAvatarInputFilter())->setData($request->getUploadedFiles());
        if (! $inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($inputFilter->getMessages());
        }

        $userAvatar = $this->userAvatarService->createAvatar(
            $request->getAttribute(User::class),
            $inputFilter->getValue('avatar')
        );

        return $this->createdResponse($request, $userAvatar);
    }
}
