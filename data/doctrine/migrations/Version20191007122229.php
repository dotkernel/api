<?php

declare(strict_types=1);

namespace DotKernelApi\Migrations;

use Api\App\Entity\UuidOrderedTimeGenerator;
use Api\User\Entity\User;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

use function password_hash;

/**
 * Class Version20191007122229
 * @package DotKernelApi\Migrations
 */
final class Version20191007122229 extends AbstractMigration
{
    const TABLE_OAUTH_CLIENTS = 'oauth_clients';
    const TABLE_OAUTH_SCOPES = 'oauth_scopes';
    const TABLE_USER = 'user';
    const TABLE_USER_DETAIL = 'user_detail';
    const TABLE_USER_ROLE = 'user_role';
    const TABLE_USER_ROLES = 'user_roles';
    const TABLE_ADMIN = 'admin';
    const TABLE_ADMIN_ROLE = 'admin_role';
    const TABLE_ADMIN_ROLES = 'admin_roles';

    /**
     * @return string
     */
    public function getDescription() : string
    {
        return 'Initial database content.';
    }

    /**
     * @param Schema $schema
     * @throws DBALException
     */
    public function up(Schema $schema) : void
    {
        /**
         * Add dummy query to avoid warning:
         * Migration 20191007122229 was executed but did not result in any SQL statements
         * Triggered when queries are not executed via the methods in AbstractMigration
         */
        $this->addSql('#');

        $now = (new DateTime())->format('Y-m-d H:i:s');
        $this->connection->insert(self::TABLE_OAUTH_CLIENTS, [
            'name' => 'frontend',
            'user_id' => null,
            'secret' => password_hash('frontend', PASSWORD_DEFAULT),
            'redirect' => '/',
            'personal_access_client' => true,
            'password_client' => true,
            'revoked' => 0,
            'created_at' => $now
        ]);
        $this->connection->insert(self::TABLE_OAUTH_CLIENTS, [
            'name' => 'admin',
            'user_id' => null,
            'secret' => password_hash('admin', PASSWORD_DEFAULT),
            'redirect' => '/',
            'personal_access_client' => true,
            'password_client' => true,
            'revoked' => 0,
            'created_at' => $now
        ]);

        $this->connection->insert(self::TABLE_OAUTH_SCOPES, [
            'id' => 'api'
        ]);

        $userUuid = UuidOrderedTimeGenerator::generateUuid()->getBytes();
        $this->connection->insert(self::TABLE_USER, [
            'uuid' => $userUuid,
            'identity' => 'test@dotkernel.com',
            'password' => password_hash('dotkernel', PASSWORD_DEFAULT),
            'status' => User::STATUS_ACTIVE,
            'isDeleted' => 0,
            'hash' => User::generateHash(),
            'created' => $now
        ]);

        $userDetailUuid = UuidOrderedTimeGenerator::generateUuid()->getBytes();
        $this->connection->insert(self::TABLE_USER_DETAIL, [
            'uuid' => $userDetailUuid,
            'userUuid' => $userUuid,
            'firstname' => 'Test',
            'lastname' => 'Account',
            'created' => $now
        ]);

        $adminUuid = UuidOrderedTimeGenerator::generateUuid()->getBytes();
        $this->connection->insert(self::TABLE_ADMIN, [
            'uuid' => $adminUuid,
            'identity' => 'admin',
            'password' => password_hash('dotadmin', PASSWORD_DEFAULT),
            'firstName' => 'DotKernel',
            'lastName' => 'Admin',
            'status' => User::STATUS_ACTIVE,
            'created' => $now
        ]);

        $roles = [
            ['uuid' => UuidOrderedTimeGenerator::generateUuid()->getBytes(), 'name' => 'admin', 'created' => $now],
            ['uuid' => UuidOrderedTimeGenerator::generateUuid()->getBytes(), 'name' => 'member', 'created' => $now],
        ];
        foreach ($roles as $role) {
            $this->connection->insert(self::TABLE_USER_ROLE, $role);
            $this->connection->insert(self::TABLE_USER_ROLES, [
                'userUuid' => $userUuid,
                'roleUuid' => $role['uuid']
            ]);
        }

        $adminRoles = [
            ['uuid' => UuidOrderedTimeGenerator::generateUuid()->getBytes(), 'name' => 'admin', 'created' => $now],
            ['uuid' => UuidOrderedTimeGenerator::generateUuid()->getBytes(), 'name' => 'superuser', 'created' => $now],
        ];
        foreach ($adminRoles as $adminRole) {
            $this->connection->insert(self::TABLE_ADMIN_ROLE, $adminRole);
            $this->connection->insert(self::TABLE_ADMIN_ROLES, [
                'userUuid' => $adminUuid,
                'roleUuid' => $adminRole['uuid']
            ]);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->addSql("TRUNCATE TABLE " . self::TABLE_ADMIN_ROLES);
        $this->addSql("TRUNCATE TABLE " . self::TABLE_ADMIN_ROLE);
        $this->addSql("TRUNCATE TABLE " . self::TABLE_ADMIN);
        $this->addSql("TRUNCATE TABLE " . self::TABLE_USER_ROLES);
        $this->addSql("TRUNCATE TABLE " . self::TABLE_USER_ROLE);
        $this->addSql("TRUNCATE TABLE " . self::TABLE_USER_DETAIL);
        $this->addSql("TRUNCATE TABLE " . self::TABLE_USER);
        $this->addSql("TRUNCATE TABLE " . self::TABLE_OAUTH_SCOPES);
        $this->addSql("TRUNCATE TABLE " . self::TABLE_OAUTH_CLIENTS);
    }
}
