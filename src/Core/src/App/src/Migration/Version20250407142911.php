<?php

declare(strict_types=1);

namespace Core\App\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250407142911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE admin (identity VARCHAR(191) NOT NULL, firstName VARCHAR(191) DEFAULT NULL, lastName VARCHAR(191) DEFAULT NULL, password VARCHAR(191) NOT NULL, status ENUM('active', 'inactive') DEFAULT 'active' NOT NULL, uuid BINARY(16) NOT NULL, created DATETIME NOT NULL, updated DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_880E0D766A95E9C4 (identity), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE admin_roles (userUuid BINARY(16) NOT NULL, roleUuid BINARY(16) NOT NULL, INDEX IDX_1614D53DD73087E9 (userUuid), INDEX IDX_1614D53D88446210 (roleUuid), PRIMARY KEY(userUuid, roleUuid)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE admin_login (identity VARCHAR(191) DEFAULT NULL, adminIp VARCHAR(191) DEFAULT NULL, country VARCHAR(191) DEFAULT NULL, continent VARCHAR(191) DEFAULT NULL, organization VARCHAR(191) DEFAULT NULL, deviceType VARCHAR(191) DEFAULT NULL, deviceBrand VARCHAR(191) DEFAULT NULL, deviceModel VARCHAR(40) DEFAULT NULL, isMobile ENUM('yes', 'no') DEFAULT NULL, osName VARCHAR(191) DEFAULT NULL, osVersion VARCHAR(191) DEFAULT NULL, osPlatform VARCHAR(191) DEFAULT NULL, clientType VARCHAR(191) DEFAULT NULL, clientName VARCHAR(191) DEFAULT NULL, clientEngine VARCHAR(191) DEFAULT NULL, clientVersion VARCHAR(191) DEFAULT NULL, loginStatus ENUM('success', 'fail') DEFAULT NULL, uuid BINARY(16) NOT NULL, created DATETIME NOT NULL, updated DATETIME DEFAULT NULL, PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE admin_role (name ENUM('admin', 'superuser') DEFAULT 'admin' NOT NULL, uuid BINARY(16) NOT NULL, created DATETIME NOT NULL, updated DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_7770088A5E237E06 (name), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE oauth_access_tokens (id INT UNSIGNED AUTO_INCREMENT NOT NULL, user_id VARCHAR(25) DEFAULT NULL, token VARCHAR(100) NOT NULL, revoked TINYINT(1) DEFAULT 0 NOT NULL, expires_at DATETIME NOT NULL, client_id INT UNSIGNED DEFAULT NULL, INDEX IDX_CA42527C19EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE oauth_access_token_scopes (access_token_id INT UNSIGNED NOT NULL, scope_id INT UNSIGNED NOT NULL, INDEX IDX_9FDF62E92CCB2688 (access_token_id), INDEX IDX_9FDF62E9682B5931 (scope_id), PRIMARY KEY(access_token_id, scope_id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE oauth_auth_codes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, revoked TINYINT(1) DEFAULT 0 NOT NULL, expiresDatetime DATETIME DEFAULT NULL, client_id INT UNSIGNED DEFAULT NULL, INDEX IDX_BB493F8319EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE oauth_auth_code_scopes (auth_code_id INT UNSIGNED NOT NULL, scope_id INT UNSIGNED NOT NULL, INDEX IDX_988BFFBF69FEDEE4 (auth_code_id), INDEX IDX_988BFFBF682B5931 (scope_id), PRIMARY KEY(auth_code_id, scope_id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE oauth_clients (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(40) NOT NULL, secret VARCHAR(100) DEFAULT NULL, redirect VARCHAR(191) NOT NULL, revoked TINYINT(1) DEFAULT 0 NOT NULL, isConfidential TINYINT(1) DEFAULT 0 NOT NULL, user_id BINARY(16) DEFAULT NULL, INDEX IDX_13CE8101A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE oauth_refresh_tokens (id INT UNSIGNED AUTO_INCREMENT NOT NULL, revoked TINYINT(1) DEFAULT 0 NOT NULL, expires_at DATETIME NOT NULL, access_token_id INT UNSIGNED DEFAULT NULL, INDEX IDX_5AB6872CCB2688 (access_token_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE oauth_scopes (id INT UNSIGNED AUTO_INCREMENT NOT NULL, scope VARCHAR(191) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE settings (identifier ENUM('table_admin_list_selected_columns', 'table_admin_list_logins_selected_columns', 'table_user_list_selected_columns') NOT NULL, value LONGTEXT NOT NULL, uuid BINARY(16) NOT NULL, created DATETIME NOT NULL, updated DATETIME DEFAULT NULL, admin_uuid BINARY(16) DEFAULT NULL, INDEX IDX_E545A0C5F166D246 (admin_uuid), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (identity VARCHAR(191) NOT NULL, password VARCHAR(191) NOT NULL, status ENUM('active', 'pending', 'deleted') DEFAULT 'pending' NOT NULL, hash VARCHAR(191) NOT NULL, uuid BINARY(16) NOT NULL, created DATETIME NOT NULL, updated DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D6496A95E9C4 (identity), UNIQUE INDEX UNIQ_8D93D649D1B862B8 (hash), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_roles (userUuid BINARY(16) NOT NULL, roleUuid BINARY(16) NOT NULL, INDEX IDX_54FCD59FD73087E9 (userUuid), INDEX IDX_54FCD59F88446210 (roleUuid), PRIMARY KEY(userUuid, roleUuid)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_avatar (name VARCHAR(191) NOT NULL, uuid BINARY(16) NOT NULL, created DATETIME NOT NULL, updated DATETIME DEFAULT NULL, userUuid BINARY(16) DEFAULT NULL, UNIQUE INDEX UNIQ_73256912D73087E9 (userUuid), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_detail (firstName VARCHAR(191) DEFAULT NULL, lastName VARCHAR(191) DEFAULT NULL, email VARCHAR(191) NOT NULL, uuid BINARY(16) NOT NULL, created DATETIME NOT NULL, updated DATETIME DEFAULT NULL, userUuid BINARY(16) DEFAULT NULL, UNIQUE INDEX UNIQ_4B5464AED73087E9 (userUuid), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_reset_password (expires DATETIME NOT NULL, hash VARCHAR(191) NOT NULL, status ENUM('completed', 'requested') DEFAULT 'requested' NOT NULL, uuid BINARY(16) NOT NULL, created DATETIME NOT NULL, updated DATETIME DEFAULT NULL, userUuid BINARY(16) DEFAULT NULL, UNIQUE INDEX UNIQ_D21DE3BCD1B862B8 (hash), INDEX IDX_D21DE3BCD73087E9 (userUuid), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_role (name ENUM('guest', 'user') DEFAULT 'user' NOT NULL, uuid BINARY(16) NOT NULL, created DATETIME NOT NULL, updated DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_2DE8C6A35E237E06 (name), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE admin_roles ADD CONSTRAINT FK_1614D53DD73087E9 FOREIGN KEY (userUuid) REFERENCES admin (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE admin_roles ADD CONSTRAINT FK_1614D53D88446210 FOREIGN KEY (roleUuid) REFERENCES admin_role (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_access_tokens ADD CONSTRAINT FK_CA42527C19EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_access_token_scopes ADD CONSTRAINT FK_9FDF62E92CCB2688 FOREIGN KEY (access_token_id) REFERENCES oauth_access_tokens (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_access_token_scopes ADD CONSTRAINT FK_9FDF62E9682B5931 FOREIGN KEY (scope_id) REFERENCES oauth_scopes (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_auth_codes ADD CONSTRAINT FK_BB493F8319EB6921 FOREIGN KEY (client_id) REFERENCES oauth_clients (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_auth_code_scopes ADD CONSTRAINT FK_988BFFBF69FEDEE4 FOREIGN KEY (auth_code_id) REFERENCES oauth_auth_codes (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_auth_code_scopes ADD CONSTRAINT FK_988BFFBF682B5931 FOREIGN KEY (scope_id) REFERENCES oauth_scopes (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_clients ADD CONSTRAINT FK_13CE8101A76ED395 FOREIGN KEY (user_id) REFERENCES user (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_refresh_tokens ADD CONSTRAINT FK_5AB6872CCB2688 FOREIGN KEY (access_token_id) REFERENCES oauth_access_tokens (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE settings ADD CONSTRAINT FK_E545A0C5F166D246 FOREIGN KEY (admin_uuid) REFERENCES admin (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_roles ADD CONSTRAINT FK_54FCD59FD73087E9 FOREIGN KEY (userUuid) REFERENCES user (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_roles ADD CONSTRAINT FK_54FCD59F88446210 FOREIGN KEY (roleUuid) REFERENCES user_role (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_avatar ADD CONSTRAINT FK_73256912D73087E9 FOREIGN KEY (userUuid) REFERENCES user (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_detail ADD CONSTRAINT FK_4B5464AED73087E9 FOREIGN KEY (userUuid) REFERENCES user (uuid)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_reset_password ADD CONSTRAINT FK_D21DE3BCD73087E9 FOREIGN KEY (userUuid) REFERENCES user (uuid)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE admin_roles DROP FOREIGN KEY FK_1614D53DD73087E9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE admin_roles DROP FOREIGN KEY FK_1614D53D88446210
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_access_tokens DROP FOREIGN KEY FK_CA42527C19EB6921
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_access_token_scopes DROP FOREIGN KEY FK_9FDF62E92CCB2688
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_access_token_scopes DROP FOREIGN KEY FK_9FDF62E9682B5931
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_auth_codes DROP FOREIGN KEY FK_BB493F8319EB6921
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_auth_code_scopes DROP FOREIGN KEY FK_988BFFBF69FEDEE4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_auth_code_scopes DROP FOREIGN KEY FK_988BFFBF682B5931
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_clients DROP FOREIGN KEY FK_13CE8101A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_refresh_tokens DROP FOREIGN KEY FK_5AB6872CCB2688
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE settings DROP FOREIGN KEY FK_E545A0C5F166D246
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_roles DROP FOREIGN KEY FK_54FCD59FD73087E9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_roles DROP FOREIGN KEY FK_54FCD59F88446210
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_avatar DROP FOREIGN KEY FK_73256912D73087E9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_detail DROP FOREIGN KEY FK_4B5464AED73087E9
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_reset_password DROP FOREIGN KEY FK_D21DE3BCD73087E9
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE admin
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE admin_roles
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE admin_login
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE admin_role
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE oauth_access_tokens
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE oauth_access_token_scopes
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE oauth_auth_codes
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE oauth_auth_code_scopes
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE oauth_clients
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE oauth_refresh_tokens
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE oauth_scopes
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE settings
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_roles
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_avatar
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_detail
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_reset_password
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_role
        SQL);
    }
}
