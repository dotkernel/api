<?php

declare(strict_types=1);

namespace Api\User\Handler\Account\Avatar;

use Api\App\Exception\BadRequestException;
use Api\App\Handler\AbstractHandler;
use Api\App\IdentityInterface;
use Api\User\InputFilter\UpdateAvatarInputFilter;
use Api\User\Service\UserAvatarServiceInterface;
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
        $result = $this->inputFilter->validate($request->getUploadedFiles());
        if (! $result->valid()) {
            throw BadRequestException::fromValidationResult($result);
        }

        return $this->createdResponse(
            $request,
            $this->userAvatarService->saveAvatar(
                $request->getAttribute(IdentityInterface::class),
                $result->value()['avatar']
            )
        );
    }
}
