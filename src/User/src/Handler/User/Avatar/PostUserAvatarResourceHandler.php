<?php

declare(strict_types=1);

namespace Api\User\Handler\User\Avatar;

use Api\App\Attribute\Resource;
use Api\App\Exception\BadRequestException;
use Api\App\Handler\AbstractHandler;
use Api\User\InputFilter\UpdateAvatarInputFilter;
use Api\User\Service\UserAvatarServiceInterface;
use Core\User\Entity\User;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostUserAvatarResourceHandler extends AbstractHandler
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
    #[Resource(entity: User::class)]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $result = $this->inputFilter->validate($request->getUploadedFiles());
        if (! $result->valid()) {
            throw BadRequestException::fromValidationResult($result);
        }

        return $this->createdResponse(
            $request,
            $this->userAvatarService->saveAvatar(
                $request->getAttribute(User::class),
                $result->value()['avatar']
            )
        );
    }
}
