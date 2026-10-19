<?php

declare(strict_types=1);

namespace Api\Admin\InputFilter;

use Api\App\InputFilter\Input\UuidInput;
use Core\App\InputFilter\AbstractInputFilter;
use Laminas\InputFilter\Factory;

/**
 * @phpstan-type AdminRoleDataType array{
 *     id: non-empty-string,
 * }
 * @extends AbstractInputFilter<AdminRoleDataType>
 */
class AdminRoleInputFilter extends AbstractInputFilter
{
    public function __construct(Factory $factory)
    {
        parent::__construct($factory);

        $this->add(UuidInput::spec('id'));
    }
}
