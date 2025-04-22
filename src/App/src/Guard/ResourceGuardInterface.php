<?php

declare(strict_types=1);

namespace Api\App\Guard;

use Core\App\Entity\EntityInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ResourceGuardInterface
{
    public function __invoke(
        ServerRequestInterface $request,
        EntityManagerInterface $entityManager,
        EntityInterface $entity
    ): void;
}
