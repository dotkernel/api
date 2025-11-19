<?php

declare(strict_types=1);

namespace Core\App\Doctrine;

use BackedEnum;
use Core\App\DBAL\Types\AbstractEnumType;
use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\Migrations\Events;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

use function array_column;
use function array_key_exists;
use function implode;
use function in_array;
use function sprintf;

class MigrationsMigratedSubscriber implements EventSubscriber
{
    private EntityManagerInterface $entityManager;
    private Connection $connection;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
        $this->entityManager = $container->get('doctrine.entity_manager.orm_default');
        $this->connection    = $this->entityManager->getConnection();
    }

    /**
     * @throws Exception
     */
    public function getSubscribedEvents(): array
    {
        if (! $this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            return [];
        }

        return [
            Events::onMigrationsMigrating,
        ];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function onMigrationsMigrating(): void
    {
        $dbEnumTypes = $this->getCustomEnumTypesFromTheDatabase();
        $fsEnumTypes = $this->getCustomEnumTypesFromTheFileSystem();

        $enumTypes = $this->mergeCustomEnumTypes($dbEnumTypes, $fsEnumTypes);
        foreach ($enumTypes as $action => $enums) {
            foreach ($enums as $type => $values) {
                match ($action) {
                    'create' => $this->createDatabaseType($type, $values),
                    'delete' => $this->deleteDatabaseType($type),
                    'update' => $this->updateDatabaseType($type, $values),
                    default  => null,
                };
            }
        }
    }

    /**
     * @phpstan-return array<non-empty-string, AbstractEnumType>
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getCustomEnumTypesFromTheFileSystem(): array
    {
        $enumTypes = [];

        $customTypes = $this->container->get('config')['doctrine']['types'] ?? [];
        foreach ($customTypes as $type => $class) {
            $class = new $class();
            if (! $class instanceof AbstractEnumType) {
                continue;
            }
            $enumTypes[$type] = $class;
        }

        return $enumTypes;
    }

    /**
     * @phpstan-return list<non-empty-string>
     * @throws Exception
     */
    private function getDatabaseTypeValues(string $type): array
    {
        $results = $this->connection->executeQuery(
            "SELECT e.enumlabel FROM pg_type t JOIN pg_enum e ON t.oid = e.enumtypid WHERE t.typname = '$type';"
        )->fetchAllAssociative();

        return array_column($results, 'enumlabel');
    }

    /**
     * @return list<non-empty-string>
     * @throws Exception
     */
    private function getCustomEnumTypesFromTheDatabase(): array
    {
        return $this->connection->executeQuery(
            'SELECT t.typname FROM pg_type t JOIN pg_enum e ON t.oid = e.enumtypid GROUP BY t.typname'
        )->fetchFirstColumn();
    }

    /**
     * @param list<non-empty-string> $dbEnumTypes
     * @param array<non-empty-string, AbstractEnumType> $fsEnumTypes
     * @return array{
     *     create: array<non-empty-string, list<BackedEnum>>,
     *     delete: array<non-empty-string, list<non-empty-string>>,
     *     skip: array<non-empty-string, list<BackedEnum>>,
     *     update: array<non-empty-string, list<BackedEnum>>
     * }
     * @throws Exception
     */
    private function mergeCustomEnumTypes(array $dbEnumTypes, array $fsEnumTypes): array
    {
        $enumTypes = [
            'create' => [],
            'delete' => [],
            'skip'   => [],
            'update' => [],
        ];

        /** @var AbstractEnumType $class */
        foreach ($fsEnumTypes as $type => $class) {
            $fsTypeValues = $class->getEnumValues();
            if (in_array($type, $dbEnumTypes)) {
                $dbTypeValues = $this->getDatabaseTypeValues($type);
                if ($dbTypeValues === $fsTypeValues) {
                    $enumTypes['skip'][$type] = $fsTypeValues;
                } else {
                    $enumTypes['update'][$type] = $fsTypeValues;
                }
            } else {
                $enumTypes['create'][$type] = $fsTypeValues;
            }
        }

        foreach ($dbEnumTypes as $type) {
            if (! array_key_exists($type, $fsEnumTypes)) {
                $enumTypes['delete'][$type] = $this->getDatabaseTypeValues($type);
            }
        }

        return $enumTypes;
    }

    /**
     * @param non-empty-string $type
     * @param list<non-empty-string> $values
     * @throws Exception
     */
    private function createDatabaseType(string $type, array $values): void
    {
        $sql = sprintf("CREATE TYPE %s AS ENUM ('%s');", $type, implode("', '", $values));
        $this->connection->executeQuery($sql);
    }

    /**
     * @throws Exception
     */
    private function deleteDatabaseType(string $type): void
    {
        $sql = sprintf('DROP TYPE %s;', $type);
        $this->connection->executeQuery($sql);
    }

    /**
     * @param non-empty-string $type
     * @param list<non-empty-string> $values
     * @throws Exception
     */
    private function updateDatabaseType(string $type, array $values): void
    {
        $this->deleteDatabaseType($type);
        $this->createDatabaseType($type, $values);
    }
}
