<?php

declare(strict_types=1);

namespace Api\User\Handler;

use Api\App\Exception\BadRequestException;
use Api\App\Exception\ConflictException;
use Api\App\Exception\NotFoundException;
use Api\App\Handler\HandlerTrait;
use Api\App\Message;
use Api\User\Entity\User;
use Api\User\InputFilter\CreateUserInputFilter;
use Api\User\InputFilter\UpdateUserInputFilter;
use Api\User\Service\UserServiceInterface;
use Dot\AnnotatedServices\Annotation\Inject;
use Dot\Mail\Exception\MailException;
use Mezzio\Hal\HalResponseFactory;
use Mezzio\Hal\ResourceGenerator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

use function sprintf;

class UserHandler implements RequestHandlerInterface
{
    use HandlerTrait;

    /**
     * @Inject({
     *     HalResponseFactory::class,
     *     ResourceGenerator::class,
     *     UserServiceInterface::class,
     *     "config"
     * })
     */
    public function __construct(
        protected HalResponseFactory $responseFactory,
        protected ResourceGenerator $resourceGenerator,
        protected UserServiceInterface $userService,
        protected array $config,
    ) {
    }

    /**
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $uuid = $request->getAttribute('uuid');
        $user = $this->userService->findOneBy(['uuid' => $uuid]);
        if (! $user instanceof User) {
            throw new NotFoundException(sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid));
        }

        $this->userService->deleteUser($user);

        return $this->noContentResponse();
    }

    /**
     * @throws NotFoundException
     */
    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $uuid = $request->getAttribute('uuid');
        $user = $this->userService->findOneBy(['uuid' => $uuid]);
        if (! $user instanceof User) {
            throw new NotFoundException(sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid));
        }

        return $this->createResponse($request, $user);
    }

    public function getCollection(ServerRequestInterface $request): ResponseInterface
    {
        return $this->createResponse($request, $this->userService->getUsers($request->getQueryParams()));
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     * @throws RuntimeException
     */
    public function patch(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new UpdateUserInputFilter())->setData((array) $request->getParsedBody());
        if (! $inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($inputFilter->getMessages());
        }

        $uuid = $request->getAttribute('uuid');
        $user = $this->userService->findOneBy(['uuid' => $uuid]);
        if (! $user instanceof User) {
            throw new NotFoundException(sprintf(Message::NOT_FOUND_BY_UUID, 'user', $uuid));
        }

        $user = $this->userService->updateUser($user, $inputFilter->getValues());

        return $this->createResponse($request, $user);
    }

    /**
     * @throws BadRequestException
     * @throws ConflictException
     * @throws MailException
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $inputFilter = (new CreateUserInputFilter())->setData((array) $request->getParsedBody());
        if (! $inputFilter->isValid()) {
            throw (new BadRequestException())->setMessages($inputFilter->getMessages());
        }

        $user = $this->userService->createUser($inputFilter->getValues());
        if ($user->isPending()) {
            $this->userService->sendActivationMail($user);
        } elseif ($user->isActive()) {
            $this->userService->sendWelcomeMail($user);
        }

        return $this->createdResponse($request, $user);
    }
}
