<?php

declare(strict_types=1);

namespace Core\App\Event;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Dot\DependencyInjection\Attribute\Inject;

use function array_key_exists;
use function is_string;

class TablePrefixEventListener
{
    private string $prefix = '';

    /**
     * @phpstan-param array<non-empty-string, mixed> $config
     */
    #[Inject(
        'config.doctrine.connection.orm_default.params',
    )]
    public function __construct(array $config)
    {
        if (array_key_exists('table_prefix', $config) && is_string($config['table_prefix'])) {
            $this->prefix = $config['table_prefix'];
        }
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        if ($this->prefix === '') {
            return;
        }

        $classMetadata = $eventArgs->getClassMetadata();
        if (
            ! $classMetadata->isInheritanceTypeSingleTable()
            || $classMetadata->getName() === $classMetadata->rootEntityName
        ) {
            $classMetadata->setPrimaryTable([
                'name' => $this->prefix . $classMetadata->getTableName(),
            ]);
        }

        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if ($mapping['type'] === ClassMetadata::MANY_TO_MANY && $mapping['isOwningSide']) {
                $classMetadata->associationMappings[$fieldName]['joinTable']['name'] =
                    $this->prefix . $mapping['joinTable']['name'];
            }
        }
    }
}
