<?php

declare(strict_types=1);

namespace Core\App\Repository;

use Core\App\Entity\EntityInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends EntityRepository<object>
 */
abstract class AbstractRepository extends EntityRepository
{
    public function deleteResource(EntityInterface $resource): void
    {
        $this->getEntityManager()->remove($resource);
        $this->getEntityManager()->flush();
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->getEntityManager()->createQueryBuilder();
    }

    public function saveResource(EntityInterface $resource): void
    {
        $this->getEntityManager()->persist($resource);
        $this->getEntityManager()->flush();
    }
}
