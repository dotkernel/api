<?php

declare(strict_types=1);

namespace Api\User\Handler\User;

use Api\App\Attribute\Resource;
use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\User\InputFilter\UpdateUserInputFilter;
use Api\User\Service\UserServiceInterface;
use Core\User\Entity\User;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PatchUserResourceHandler extends AbstractHandler
{
    #[Inject(
        UserServiceInterface::class,
        UpdateUserInputFilter::class,
    )]
    public function __construct(
        protected UserServiceInterface $userService,
        protected UpdateUserInputFilter $inputFilter,
    ) {
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    #[Resource(entity: User::class)]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $result = $this->inputFilter->validate((array) $request->getParsedBody());
        if (! $result->valid()) {
            throw BadRequestException::fromValidationResult($result);
        }

        /** @var non-empty-array<non-empty-string, mixed> $data */
        $data = $result->value();

        return $this->createResponse(
            $request,
            $this->userService->saveUser($data, $request->getAttribute(User::class))
        );
    }
}
