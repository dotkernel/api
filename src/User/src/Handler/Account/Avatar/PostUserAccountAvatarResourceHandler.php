<?php

declare(strict_types=1);

namespace Api\User\Handler\Account\Avatar;

use Api\App\Exception\BadRequestException;
use Api\App\Handler\AbstractHandler;
use Api\App\IdentityInterface;
use Api\User\InputFilter\UpdateAvatarInputFilter;
use Api\User\Service\UserAvatarServiceInterface;
use Core\App\Message;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostUserAccountAvatarResourceHandler extends AbstractHandler
{
    #[Inject(
        UserAvatarServiceInterface::class,
        UpdateAvatarInputFilter::class,
    )]
    public function __construct(
        protected UserAvatarServiceInterface $userAvatarService,
        protected UpdateAvatarInputFilter $inputFilter,
    ) {
    }

    /**
     * @throws BadRequestException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->inputFilter->setData($request->getUploadedFiles());
        if (! $this->inputFilter->isValid()) {
            throw BadRequestException::create(
                detail: Message::VALIDATOR_INVALID_DATA,
                additional: ['errors' => $this->inputFilter->getMessages()]
            );
        }

        return $this->createdResponse(
            $request,
            $this->userAvatarService->saveAvatar(
                $request->getAttribute(IdentityInterface::class),
                $this->inputFilter->getValue('avatar')
            )
        );
    }
}
