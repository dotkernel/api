<?php

declare(strict_types=1);

namespace Api\App\Collection;

use Doctrine\ORM\Tools\Pagination\Paginator;

class ResourceCollection extends Paginator implements CollectionInterface
{}
