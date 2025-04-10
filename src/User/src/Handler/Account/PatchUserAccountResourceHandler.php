<?php

declare(strict_types=1);

namespace Api\User\Handler\Account;

use Api\App\Handler\AbstractHandler;
use Api\User\InputFilter\UpdateUserInputFilter;
use Api\User\Service\UserServiceInterface;
use Core\App\Exception\BadRequestException;
use Core\App\Exception\ConflictException;
use Core\App\Exception\NotFoundException;
use Core\User\Entity\User;
use Dot\DependencyInjection\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PatchUserAccountResourceHandler extends AbstractHandler
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
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->inputFilter
            ->setValidationGroup(['password', 'passwordConfirm', 'detail'])
            ->setData((array) $request->getParsedBody());
        if (! $this->inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($this->inputFilter->getMessages());
        }

        return $this->createResponse(
            $request,
            $this->userService->saveUser(
                (array) $this->inputFilter->getValues(),
                $request->getAttribute(User::class)
            )
        );
    }
}
