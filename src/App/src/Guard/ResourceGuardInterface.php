<?php

declare(strict_types=1);

namespace Api\App\Guard;

use Core\App\Entity\EntityInterface;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;

interface ResourceGuardInterface
{
    public function __invoke(
        EntityManagerInterface $entityManager,
        UserEntityInterface $currentUser,
        EntityInterface $entity
    ): void;
}
