<?php

declare(strict_types=1);

namespace Api\User\InputFilter;

use Api\App\InputFilter\Input\UuidInput;
use Core\App\InputFilter\AbstractInputFilter;
use Laminas\InputFilter\Factory;

/**
 * @phpstan-type UserRoleDataType array{
 *     id: non-empty-string,
 * }
 * @extends AbstractInputFilter<UserRoleDataType>
 */
class UserRoleInputFilter extends AbstractInputFilter
{
    public function __construct(Factory $factory)
    {
        parent::__construct($factory);

        $this->add(UuidInput::spec('id'));
    }
}
