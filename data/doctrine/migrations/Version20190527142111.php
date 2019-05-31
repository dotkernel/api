<?php

declare(strict_types=1);

namespace DotKernelApi\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Version\Version;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190527142111 extends AbstractMigration
{
    public function __construct(Version $version)
    {
        parent::__construct($version);

        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );
    }

    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('CREATE TABLE oauth_access_tokens (
            id VARCHAR(100) NOT NULL COLLATE utf8mb4_general_ci, 
            user_id VARCHAR(40) DEFAULT NULL COLLATE utf8mb4_general_ci, 
            client_id VARCHAR(40) DEFAULT NULL COLLATE utf8mb4_general_ci, 
            name VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_general_ci, 
            scopes TEXT DEFAULT NULL COLLATE utf8mb4_general_ci, 
            revoked TINYINT(1) DEFAULT NULL, 
            created_at DATETIME DEFAULT NULL, 
            updated_at DATETIME DEFAULT NULL, 
            expires_at DATETIME DEFAULT NULL, 
            INDEX idx1_oauth_access_tokens (user_id), 
            PRIMARY KEY(id)) DEFAULT CHARSET=utf8mb4 ENGINE = InnoDB COMMENT = \'\' '
        );

        $this->addSql('CREATE TABLE oauth_auth_codes (
            id VARCHAR(100) NOT NULL COLLATE utf8mb4_general_ci, 
            user_id INT DEFAULT NULL, 
            client_id INT DEFAULT NULL, 
            scopes TEXT DEFAULT NULL COLLATE utf8mb4_general_ci, 
            revoked TINYINT(1) DEFAULT NULL, 
            expires_at DATETIME DEFAULT NULL, 
            PRIMARY KEY(id)) DEFAULT CHARSET=utf8mb4 ENGINE = InnoDB COMMENT = \'\' '
        );

        $this->addSql('CREATE TABLE oauth_clients (
            name VARCHAR(40) NOT NULL COLLATE utf8mb4_general_ci, 
            user_id INT DEFAULT NULL, 
            secret VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_general_ci, 
            redirect VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_general_ci, 
            personal_access_client TINYINT(1) DEFAULT NULL, 
            password_client TINYINT(1) DEFAULT NULL, 
            revoked TINYINT(1) DEFAULT NULL, 
            created_at DATETIME DEFAULT NULL, 
            updated_at DATETIME DEFAULT NULL, 
            INDEX idx1_oauth_clients (user_id), 
            PRIMARY KEY(name)) DEFAULT CHARSET=utf8mb4 ENGINE = InnoDB COMMENT = \'\' '
        );

        $this->addSql('CREATE TABLE oauth_personal_access_clients (
            client_id INT DEFAULT NULL, 
            created_at DATETIME DEFAULT NULL, 
            updated_at DATETIME DEFAULT NULL, 
            INDEX idx1_oauth_personal_access_clients (client_id)) 
            DEFAULT CHARSET=utf8mb4 ENGINE = InnoDB COMMENT = \'\' '
        );

        $this->addSql('CREATE TABLE oauth_refresh_tokens (
            id VARCHAR(100) NOT NULL COLLATE utf8mb4_general_ci, 
            access_token_id VARCHAR(100) DEFAULT NULL COLLATE utf8mb4_general_ci, 
            revoked TINYINT(1) DEFAULT NULL, 
            expires_at DATETIME DEFAULT NULL, 
            INDEX idx1_oauth_refresh_tokens (access_token_id), 
            PRIMARY KEY(id)) DEFAULT CHARSET=utf8mb4 ENGINE = InnoDB COMMENT = \'\' '
        );

        $this->addSql('CREATE TABLE oauth_scopes (
            id VARCHAR(30) NOT NULL COLLATE utf8mb4_general_ci, 
            PRIMARY KEY(id)) DEFAULT CHARSET=utf8mb4 ENGINE = InnoDB COMMENT = \'\' '
        );

        $this->addSql('CREATE TABLE user (
            uuid BINARY(16) NOT NULL, 
            avatarUuid BINARY(16) DEFAULT NULL, 
            detailUuid BINARY(16) DEFAULT NULL, 
            username VARCHAR(40) NOT NULL COLLATE utf8mb4_general_ci, 
            password VARCHAR(100) NOT NULL COLLATE utf8mb4_general_ci, 
            status VARCHAR(255) DEFAULT \'pending\' NOT NULL COLLATE utf8mb4_general_ci, 
            created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, 
            updated DATETIME DEFAULT NULL, 
            INDEX FK__user__user_avatar (avatarUuid), 
            UNIQUE INDEX username (username), INDEX FK__user__user_detail (detailUuid), 
            PRIMARY KEY(uuid)) DEFAULT CHARSET=utf8mb4 ENGINE = InnoDB COMMENT = \'\' '
        );

        $this->addSql('CREATE TABLE user_avatar (
            uuid BINARY(16) NOT NULL, 
            name VARCHAR(255) NOT NULL COLLATE utf8mb4_general_ci, 
            created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, 
            updated DATETIME DEFAULT NULL, 
            PRIMARY KEY(uuid)) DEFAULT CHARSET=utf8mb4 ENGINE = InnoDB COMMENT = \'\' '
        );

        $this->addSql('CREATE TABLE user_detail (
            uuid BINARY(16) NOT NULL, 
            firstname VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_general_ci, 
            lastname VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_general_ci, 
            created DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, 
            updated DATETIME DEFAULT NULL, 
            PRIMARY KEY(uuid)) DEFAULT CHARSET=utf8mb4 ENGINE = InnoDB COMMENT = \'\' '
        );

        $this->addSql('CREATE TABLE user_role (
            uuid BINARY(16) NOT NULL, 
            name VARCHAR(150) NOT NULL COLLATE utf8mb4_general_ci, 
            created DATETIME DEFAULT CURRENT_TIMESTAMP, 
            updated DATETIME DEFAULT NULL, 
            UNIQUE INDEX name (name), 
            PRIMARY KEY(uuid)) DEFAULT CHARSET=utf8mb4 ENGINE = InnoDB COMMENT = \'\' '
        );

        $this->addSql('CREATE TABLE user_roles (
            userUuid BINARY(16) NOT NULL, 
            roleUuid BINARY(16) NOT NULL, 
            INDEX FK__user_roles__user (userUuid), 
            INDEX FK__user_roles__user_role (roleUuid)) 
            DEFAULT CHARSET=utf8mb4 ENGINE = InnoDB COMMENT = \'\' '
        );
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE oauth_access_tokens');

        $this->addSql('DROP TABLE oauth_auth_codes');

        $this->addSql('DROP TABLE oauth_clients');

        $this->addSql('DROP TABLE oauth_personal_access_clients');

        $this->addSql('DROP TABLE oauth_refresh_tokens');

        $this->addSql('DROP TABLE oauth_scopes');

        $this->addSql('DROP TABLE user');

        $this->addSql('DROP TABLE user_avatar');

        $this->addSql('DROP TABLE user_detail');

        $this->addSql('DROP TABLE user_role');

        $this->addSql('DROP TABLE user_roles');
    }
}
