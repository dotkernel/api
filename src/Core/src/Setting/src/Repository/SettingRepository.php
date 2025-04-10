<?php

declare(strict_types=1);

namespace Core\Setting\Repository;

use Core\App\Repository\AbstractRepository;
use Core\Setting\Entity\Setting;
use Dot\DependencyInjection\Attribute\Entity;

#[Entity(name: Setting::class)]
class SettingRepository extends AbstractRepository
{
}
