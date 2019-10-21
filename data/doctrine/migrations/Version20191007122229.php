<?php

declare(strict_types=1);

namespace DotKernelApi\Migrations;

use Api\App\Common\UuidOrderedTimeGenerator;
use Api\User\Entity\UserEntity;
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

        $now = (new \DateTime())->format('Y-m-d H:i:s');
        $this->connection->insert(self::TABLE_OAUTH_CLIENTS, [
            'name' => 'dotkernel',
            'user_id' => null,
            'secret' => password_hash('dotkernel', PASSWORD_DEFAULT),
            'redirect' => '/',
            'personal_access_client' => true,
            'password_client' => true,
            'revoked' => false,
            'created_at' => $now
        ]);

        $this->connection->insert(self::TABLE_OAUTH_SCOPES, [
            'id' => 'api'
        ]);

        $userUuid = UuidOrderedTimeGenerator::generateUuid()->getBytes();
        $this->connection->insert(self::TABLE_USER, [
            'uuid' => $userUuid,
            'username' => 'test@dotkernel.com',
            'password' => password_hash('dotkernel', PASSWORD_DEFAULT),
            'status' => UserEntity::STATUS_ACTIVE,
            'isDeleted' => false,
            'hash' => UserEntity::generateHash(),
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
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->addSql("DELETE FROM `oauth_clients` WHERE `oauth_clients`.`name` = 'dotkernel'");
        $this->addSql("DELETE FROM `oauth_scopes` WHERE `id` = 'api'");
        $this->addSql("DELETE FROM `user_role` WHERE `user_role`.`uuid` = CAST(0x11e9e6a81f24525e9cbbb8ca3aa0178d AS BINARY)");
        $this->addSql("DELETE FROM `user_role` WHERE `user_role`.`uuid` = CAST(0x11e9e6a8238faa8ca090b8ca3aa0178d AS BINARY)");
    }
}
