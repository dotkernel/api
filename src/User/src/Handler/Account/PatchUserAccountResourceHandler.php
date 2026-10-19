<?php

declare(strict_types=1);

namespace Api\User\Handler\Account;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\AbstractHandler;
use Api\App\IdentityInterface;
use Api\User\InputFilter\UpdateUserAccountInputFilter;
use Api\User\Service\UserServiceInterface;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PatchUserAccountResourceHandler extends AbstractHandler
{
    #[Inject(
        UserServiceInterface::class,
        UpdateUserAccountInputFilter::class,
    )]
    public function __construct(
        protected UserServiceInterface $userService,
        protected UpdateUserAccountInputFilter $inputFilter,
    ) {
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
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
            $this->userService->saveUser($data, $request->getAttribute(IdentityInterface::class))
        );
    }
}
