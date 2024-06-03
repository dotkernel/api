<?php

declare(strict_types=1);

namespace Api\User\Repository;

use Doctrine\ORM\EntityRepository;
use Dot\AnnotatedServices\Annotation\Entity;

/**
 * @Entity(name="Api\User\Entity\UserResetPasswordEntity")
 * @extends EntityRepository<object>
 */
class UserResetPasswordRepository extends EntityRepository
{
}
